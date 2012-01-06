#!/usr/bin/php -q
<?php
//phpMyFireWall
//author davestj@gmail.com
//for kernels 2.6
error_reporting(E_ERROR);
include('./etc/config.php');
//set some env vars
putenv("PATH=$SYSPATHS");


//we can add a check here to see if iptables and modprobe exist
if(!file_exists($IPTABLES)){
	echo "\n\niptables not installed or wrong path has been set, \n
current binary path settings: $IPTABLES\n
please double check your iptables binary path\n
cannot continue, exiting.\n";
exit;
}
if(!file_exists($MODPROBE)){
	echo "\n\nmodprobe not installed or wrong path has been set,\n
current binary path settings: $MODPROBE\n
please double check your modprobe binary path.\n
cannot continue, exiting.\n";
	exit;
}

//get ready for command line arguments
$args = trim(next($HTTP_SERVER_VARS["argv"]));


if($args == '' || $args == '-h' || $args == '--help'){
echo "----------------------------------------------------------------------\n
	  $argv[0] usage:\n
					$argv[0] --env (display environment variables)\n
					$argv[0] --debug (display network interface and ip usage)\n
					$argv[0] --tune (tune kernel tcp stack, not used by default)\n
					$argv[0] --status (display iptables status)\n
					$argv[0] --start (start firewall)\n
					$argv[0] --stop (stop firewall)\n\n";
}
//lets parse out all interfaces shall we.
//check interface for primary ip

$MAIN_IP = shell_exec("ifconfig $ROOT_INTERFACE | grep inet | cut -d: -f2 | awk '{print $1}'");
if ($MAIN_IP == ""){
    echo "Could not determine primary ip. aborting!\n";
    exit;
}else{
	if($args == '--debug'){
echo "\n\n-------------------------------------------------------\n
Primary ip: $MAIN_IP
Root interface: $ROOT_INTERFACE\n";
 }
}
if($SERVER_IPS == ""){
    $SERVER_IPS = $MAIN_IP;
}
if($SERVER_IPS == ""){
    echo "\n\nCould not determine server ips. aborting\n!";
    exit;
}else{
    if($args == '--debug'){
	echo "server ips $SERVER_IPS\n
-------------------------------------------------------\n";
    }
}

//check nic for virtual assigned ips if any
for ($i = 0; $i <= $NUM_VIRT_INTERFACES; $i++) {

$virt_nic = explode(",",$VIRTUAL_INTERFACES);

$VIRT_IPS = shell_exec("ifconfig $virt_nic[$i] | grep inet | cut -d: -f2 | awk '{print $1}'");
if($args == '--debug'){
echo "\n\nInterface $i: $virt_nic[$i]\n";
echo "Assigned ip $i: $VIRT_IPS\n";
}

}//end for loop

//debug shell exec for env vars
if($args == '--env'){
echo shell_exec("env");
}


//check for netfilter modules in kernel
$uname = shell_exec("uname -r");
$reuname = trim($uname);

//lib/modules/2.6.16.21-0.25-default/kernel/net/ipv4/netfilter-
/*
if(!file_exists("/lib/modules/$reuname/kernel/net/ipv4/netfilter/ip_tables.ko") ||
   !file_exists("/lib/modules/$reuname/kernel/net/netfilter/xt_state.ko") ||
   !file_exists("/lib/modules/$reuname/kernel/net/ipv4/netfilter/ipt_multiport.ko")){
echo "\n\nip_tables, ipt_state, and/or ipt_multiport modules do not exist, we cant function. ABORTING!\n";
$NETFILTER = "FALSE";
exit;

}
*/
if($args == '--start'){
//put server ips into array
$srv_ip_array = explode(" ",$SERVER_IPS);
$trusted_ip_array = explode(" ",$TRUSTED_IPS);

/*
//lets load netfilters modules
if($NETFILTER != "FALSE"){
	shell_exec("$MODPROBE ip_tables");
	shell_exec("$MODPROBE xt_state");
	shell_exec("$MODPROBE ipt_multiport");
	shell_exec("$MODPROBE iptable_filter");
	//shell_exec("$MODPROBE ipt_unclean");
	shell_exec("$MODPROBE ipt_limit");
	shell_exec("$MODPROBE ipt_LOG");
	shell_exec("$MODPROBE ipt_REJECT");
	shell_exec("$MODPROBE ip_conntrack");
	shell_exec("$MODPROBE ip_conntrack_irc");
	shell_exec("$MODPROBE ip_conntrack_ftp");
	shell_exec("$MODPROBE iptable_mangle");
	shell_exec("$MODPROBE ipt_REDIRECT");
	shell_exec("$MODPROBE ipt_TOS");
	shell_exec("$MODPROBE ip_queue");
	shell_exec("$MODPROBE ipt_mark");
	shell_exec("$MODPROBE ipt_MARK");
	shell_exec("$MODPROBE ipt_tos");
	shell_exec("$MODPROBE ipt_ttl");
	shell_exec("$MODPROBE ipt_pkttype");
	shell_exec("$MODPROBE ipt_owner");
}
*/
##############################################################################
//flush existing rules if any
	shell_exec("$IPTABLES --flush");
	shell_exec("$IPTABLES -t nat --flush");
	shell_exec("$IPTABLES -t mangle --flush");

//Allow unlimited traffic on the loopback interface
	shell_exec("$IPTABLES -A INPUT  -i lo -j ACCEPT");
	shell_exec("$IPTABLES -A OUTPUT -o lo -j ACCEPT");

//Set the default policy to DROP
	shell_exec("$IPTABLES --policy INPUT   DROP");
	shell_exec("$IPTABLES --policy OUTPUT  DROP");
	shell_exec("$IPTABLES --policy FORWARD DROP");
	
	
##############################################################################
//DO NOT MODIFY THESE!
//If you set these to DROP, you will be locked out of your server.
	shell_exec("$IPTABLES -t nat --policy PREROUTING ACCEPT");
	shell_exec("$IPTABLES -t nat --policy OUTPUT ACCEPT");
	shell_exec("$IPTABLES -t nat --policy POSTROUTING ACCEPT");
	shell_exec("$IPTABLES -t mangle --policy PREROUTING ACCEPT");
	shell_exec("$IPTABLES -t mangle --policy OUTPUT ACCEPT");
	
##############################################################################
//Remove any pre-existing user-defined chains
	shell_exec("$IPTABLES --delete-chain");
	shell_exec("$IPTABLES -t nat --delete-chain");
	shell_exec("$IPTABLES -t mangle --delete-chain");


##############################################################################
// Silently Drop Stealth Scans
// All of the bits are cleared
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags ALL NONE -j DROP");

// SYN and FIN are both set
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags SYN,FIN SYN,FIN -j DROP");

// SYN and RST are both set
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags SYN,RST SYN,RST -j DROP");

// FIN and RST are both set
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags FIN,RST FIN,RST -j DROP");

// FIN is the only bit set, without the expected accompanying ACK
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags ACK,FIN FIN -j DROP");

// PSH is the only bit set, without the expected accompanying ACK
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags ACK,PSH PSH -j DROP");

// URG is the only bit set, without the expected accompanying ACK
shell_exec("$IPTABLES -A INPUT -p tcp --tcp-flags ACK,URG URG -j DROP");

##############################################################################
// Syn-flood protection
if($SYN_FLOOD_PROTECTION == "ON"){
shell_exec("$IPTABLES -N syn-flood");
shell_exec("$IPTABLES -A INPUT -p tcp --syn -j syn-flood");
shell_exec("$IPTABLES -A syn-flood -m limit --limit 10/s --limit-burst 40 -j RETURN");
shell_exec("$IPTABLES -A syn-flood -j DROP");
}
##############################################################################
// Use Connection State to Bypass Rule Checking
//
// By accepting established and related connections, we don't need to
// explicitly set various input and output rules. For example, by accepting an
// established and related output connection, we don't need to specify that
// the firewall needs to open a hole back out to client when the client
// requests SSH access.
shell_exec("$IPTABLES -A INPUT  -m state --state ESTABLISHED,RELATED -j ACCEPT");
shell_exec("$IPTABLES -A OUTPUT -m state --state ESTABLISHED,RELATED -j ACCEPT");

shell_exec("$IPTABLES -A INPUT  -m state --state INVALID -j DROP");
shell_exec("$IPTABLES -A OUTPUT -m state --state INVALID -j DROP");

##############################################################################
// Source Address Spoofing and Other Bad Addresses
// Refuse Spoofed packets pretending to be from the external interface's IP

shell_exec("for server_ips in $SERVER_IPS; do
    for subnet_broadcast in $SUBNET_BROADCAST; do
        $IPTABLES -A INPUT -i $ROOT_INTERFACE -s $server_ips -d !$subnet_broadcast -j DROP
    done
done");

##############################################################################
// Refuse packets claiming to be from a Class A private network
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $CLASS_A -j DROP");

##############################################################################
//  Refuse packets claiming to be from a Class B private network
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $CLASS_B -j DROP");

##############################################################################
//  Refuse packets claiming to be from a Class C private network
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $CLASS_C -j DROP");

##############################################################################
//  Refuse packets claiming to be from the loopback interface
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $LOOPBACK -j DROP");

##############################################################################
//  Refuse malformed broadcast packets
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $BROADCAST_DEST -j DROP");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -d $BROADCAST_SRC -j DROP");

##############################################################################
// Refuse directed broadcasts
// Used to map networks and in Denial of Service attacks
shell_exec("for subnet_base in $SUBNET_BASE; do
    $IPTABLES -A INPUT -i $ROOT_INTERFACE -d $subnet_base -j DROP
 done");
shell_exec("for subnet_broadcast in $SUBNET_BROADCAST; do
     $IPTABLES -A INPUT -i $ROOT_INTERFACE -d $subnet_broadcast -j DROP
 done");
 
##############################################################################
//  Refuse limited broadcasts
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -d $BROADCAST_DEST -j DROP");

##############################################################################
//  Refuse Class D multicast addresses - illegal as a source address
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $CLASS_D_MULTICAST -j DROP");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -p udp -d $CLASS_D_MULTICAST -j ACCEPT");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -p 2 -d $CLASS_D_MULTICAST -j ACCEPT");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -p all  -d $CLASS_D_MULTICAST -j DROP");

##############################################################################
//  Refuse Class E reserved IP addresses
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $CLASS_E_RESERVED_NET -j DROP");


##############################################################################
//  Refuse addresses defined as reserved by the IANA
//  0.*.*.*         - Can't be blocked unilaterally with DHCP
//  169.254.0.0/16  - Link Local Networks
//  192.0.2.0/24    - TEST-NET
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 0.0.0.0/8 -j DROP");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 169.254.0.0/16 -j DROP");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 192.0.2.0/24 -j DROP");


##############################################################################
// Now we can open up some holes in our firewall
//If we are not accepting 113 (ident), then we explicitly reject it!
if($USE_IDENT == "OFF"){
//   shell_exec("$IPTABLES -A INPUT -p tcp -s 0/0 -d 0/0 --dport 113 -j REJECT");
//   shell_exec("$IPTABLES -A INPUT -p udp -s 0/0 -d 0/0 --dport 113 -j REJECT");
}else{
    shell_exec("$IPTABLES -A INPUT -p tcp -s 0/0 -d 0/0 --dport 113 -j ACCEPT");
    shell_exec("$IPTABLES -A INPUT -p udp -s 0/0 -d 0/0 --dport 113 -j ACCEPT");

}

##############################################################################
// OUTPUT - PORT 113 - IDENTD

foreach($srv_ip_array as $server_ip){
	shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -s $server_ip -p tcp --syn --sport $UNPRIVPORTS --dport 113 -m state --state NEW -j REJECT --reject-with tcp-reset");
}

##############################################################################
// SYSTEM TCP IN
//put tcp in port list into array
$tcp_port_in_array = explode(" ",$SYS_TCP_IN_PORTS);
foreach($tcp_port_in_array as $tcp_in_port){
	foreach($srv_ip_array as $server_ip){
	    if($LOGGING == "ON"){
	    shell_exec("$IPTABLES -A INPUT -p tcp -m limit --limit $LOG_EVENT_PER_MINUTE/minute -i $ROOT_INTERFACE -j LOG --log-prefix '[PHPMYFIREWALL_TCP_IN_DROP]' --log-tcp-options --log-ip-options");
	    }
		shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 0/0 -d $server_ip -p tcp -m state --state NEW --sport $UNPRIVPORTS --dport $tcp_in_port -j ACCEPT");
	}
}

##############################################################################
//  SYSTEM TCP OUT
//put tcp in port list into array
$tcp_port_out_array = explode(" ",$SYS_TCP_OUT_PORTS);
foreach($tcp_port_out_array as $tcp_out_port){
    if($LOGGING == "ON"){
	 shell_exec("$IPTABLES -A OUTPUT -p tcp -m limit --limit $LOG_EVENT_PER_MINUTE/minute -o $ROOT_INTERFACE -j LOG --log-prefix '[PHPMYFIREWALL_TCP_OUT_DROP]' --log-tcp-options --log-ip-options");
    }
	 shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p tcp -m state --state NEW --sport $UNPRIVPORTS --dport $tcp_out_port -j ACCEPT");

}

##############################################################################
//  SYSTEM UDP IN
//put udp in port list into array
$udp_port_in_array = explode(" ",$SYS_UDP_IN_PORTS);
foreach($udp_port_in_array as $udp_in_port){
	foreach($srv_ip_array as $server_ip){

		shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 0/0 -d $server_ip -p udp -m state --state NEW --sport $UNPRIVPORTS --dport $udp_in_port -j ACCEPT");
	}
}

##############################################################################
//  SYSTEM UDP OUT
//put udp out port list into array
$udp_port_out_array = explode(" ",$SYS_UDP_OUT_PORTS);
foreach($udp_port_out_array as $udp_out_port){

		shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p udp -m state --state NEW --sport $UNPRIVPORTS --dport $udp_out_port -j ACCEPT");

}


##############################################################################
// CUSTOM TCP IN
//put tcp in port list into array
$custom_tcp_port_in_array = explode(" ",$CUST_TCP_IN_PORTS);
foreach($custom_tcp_port_in_array as $custom_tcp_in_port){
	foreach($srv_ip_array as $server_ip){

		shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 0/0 -d $server_ip -p tcp -m state --state NEW --sport $UNPRIVPORTS --dport $custom_tcp_in_port -j ACCEPT");
	}
}

##############################################################################
// CUSTOM TCP OUT
//put tcp out port list into array
$custom_tcp_port_out_array = explode(" ",$CUST_TCP_OUT_PORTS);
foreach($custom_tcp_port_out_array as $custom_tcp_out_port){

		shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p tcp -m state --state NEW --sport $UNPRIVPORTS --dport $custom_tcp_out_port -j ACCEPT");

}
##############################################################################
// CUSTOM UDP IN
//put udp in port list into array
$custom_udp_port_in_array = explode(" ",$CUST_UDP_IN_PORTS);
foreach($custom_udp_port_in_array as $custom_udp_in_port){
	foreach($srv_ip_array as $server_ip){

		shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s 0/0 -d $server_ip -p udp -m state --state NEW --sport $UNPRIVPORTS --dport $custom_udp_in_port -j ACCEPT");
	}
}
##############################################################################
// CUSTOM UDP OUT
//put udp out port list into array
$custom_udp_port_out_array = explode(" ",$CUST_UDP_OUT_PORTS);
foreach($custom_udp_port_out_array as $custom_udp_out_port){

		shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p udp -m state --state NEW --sport $UNPRIVPORTS --dport $custom_udp_out_port -j ACCEPT");

}

##############################################################################
// SYSTEM FEATURES FOR PING AND TRACEROUTE
//outgoing traceroutes
if($TRACE_OUT == 'ON'){
shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p udp -s $MASTER_IP --sport 32769:65535 --dport 33434:33523 -m state --state NEW -j ACCEPT");
}
##############################################################################
//outgoing ping
if($PING_OUT == 'ON'){
shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -s $MASTER_IP -m state --state NEW -p icmp --icmp-type ping -j ACCEPT");
}else{
shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -s $MASTER_IP -m state --state NEW -p icmp --icmp-type ping -j REJECT");
}
##############################################################################
//allow DNS zone transfers

shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -p udp --sport 53 --dport 53 -m state --state NEW -j ACCEPT");
shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -p tcp --sport 53 --dport 53 -m state --state NEW -j ACCEPT");
shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p udp --sport 53 --dport 53 -m state --state NEW -j ACCEPT");
shell_exec("$IPTABLES -A OUTPUT -o $ROOT_INTERFACE -p tcp --sport 53 --dport 53 -m state --state NEW -j ACCEPT");


##############################################################################
// TCP IN TRUSTED
$tcp_trusted_port_in_array = explode(" ",$TCP_IN_TRUSTED);
foreach($tcp_trusted_port_in_array as $tcp_in_trusted){
    foreach($trusted_ip_array as $trusted_ips){
    shell_exec("$IPTABLES -A INPUT -i $ROOT_INTERFACE -s $trusted_ips -d $MASTER_IP -p tcp -m state --state NEW --sport $UNPRIVPORTS --dport $tcp_in_trusted -j ACCEPT");
    }
}
##############################################################################
//Allow pinging of this server's MAIN_IP by trusted IPs only.

foreach($trusted_ip_array as $trusted_ips){
shell_exec("$IPTABLES -A INPUT -s $trusted_ips -d $MASTER_IP -i $ROOT_INTERFACE  -m state --state NEW -p icmp --icmp-type ping -j ACCEPT");
}
##############################################################################
// BANNED BOOGERS!
//opening banned ips in ban list

$fp = fopen($BLACKLIST,'r');
$fr = fread($fp,filesize ($BLACKLIST));
fclose($fp);
//parse out bad ips
$rep = trim($fr);
$banned_ip_array = explode("\n",$rep);
foreach($banned_ip_array as $banned_ip){
shell_exec("$IPTABLES -I INPUT -s $banned_ip -j DROP");

}

echo shell_exec ("$IPTABLES -L -n");
}//end the firewall start procedure


//lets tune the kernel tcp stack
if($args == '--tune'){
//Enable broadcast echo Protection
if(file_exists("/proc/sys/net/ipv4/icmp_echo_ignore_broadcasts")){
 shell_exec('echo "1" > /proc/sys/net/ipv4/icmp_echo_ignore_broadcasts');
 echo ('/proc/sys/net/ipv4/icmp_echo_ignore_broadcasts = ');echo shell_exec('cat /proc/sys/net/ipv4/icmp_echo_ignore_broadcasts');
}
//block all incoming icmp if on
if(file_exists("/proc/sys/net/ipv4/icmp_echo_ignore_all") && $PINGIN == "ON"){
shell_exec('echo "1" > /proc/sys/net/ipv4/icmp_echo_ignore_all');
 echo ('/proc/sys/net/ipv4/icmp_echo_ignore_all = ');echo shell_exec('cat /proc/sys/net/ipv4/icmp_echo_ignore_all');
}

//Disable Source Routed Packets
if(file_exists("/proc/sys/net/ipv4/conf/all/accept_source_route")){
    shell_exec('echo "0" > /proc/sys/net/ipv4/conf/all/accept_source_route');
     echo ('/proc/sys/net/ipv4/conf/all/accept_source_route = ');echo shell_exec('cat /proc/sys/net/ipv4/conf/all/accept_source_route');
}

//Enable TCP SYN Cookie Protection
if(file_exists("/proc/sys/net/ipv4/tcp_syncookies")){
    shell_exec('echo "1" > /proc/sys/net/ipv4/tcp_syncookies');
     echo ('/proc/sys/net/ipv4/tcp_syncookies = ');echo shell_exec('cat /proc/sys/net/ipv4/tcp_syncookies');
}

//Disable ICMP Redirect Acceptance
if(file_exists("/proc/sys/net/ipv4/conf/all/accept_redirects")){
    shell_exec('echo "0" > /proc/sys/net/ipv4/conf/all/accept_redirects');
     echo ('/proc/sys/net/ipv4/conf/all/accept_redirects = ');echo shell_exec('cat /proc/sys/net/ipv4/conf/all/accept_redirects');
}

//Don't send Redirect Messages
if(file_exists("/proc/sys/net/ipv4/conf/all/send_redirects")){
  shell_exec('echo "0" > /proc/sys/net/ipv4/conf/all/send_redirects');
   echo ('/proc/sys/net/ipv4/conf/all/send_redirects = ');echo shell_exec('cat /proc/sys/net/ipv4/conf/all/send_redirects');
	}
//log martians i.e. packets with impossible addresses
if(file_exists("/proc/sys/net/ipv4/conf/all/log_martians")){
  shell_exec('echo "1" > /proc/sys/net/ipv4/conf/all/log_martians');
   echo ('/proc/sys/net/ipv4/conf/all/log_martians = ');echo shell_exec('cat /proc/sys/net/ipv4/conf/all/log_martians');
	}

//Reduce DoS'ing ability by reducing timeouts
if(file_exists("/proc/sys/net/ipv4/tcp_fin_timeout")){
  shell_exec('echo "1800" > /proc/sys/net/ipv4/tcp_fin_timeout');
  echo ('/proc/sys/net/ipv4/tcp_fin_timeout = ');echo shell_exec('cat /proc/sys/net/ipv4/tcp_fin_timeout');
}

if(file_exists("/proc/sys/net/ipv4/tcp_keepalive_time")){
  shell_exec('echo "1800" > /proc/sys/net/ipv4/tcp_keepalive_time');
  echo ('/proc/sys/net/ipv4/tcp_keepalive_time = ');echo shell_exec('cat /proc/sys/net/ipv4/tcp_keepalive_time');
}

if(file_exists("/proc/sys/net/ipv4/tcp_window_scaling")){
  shell_exec('echo "1" > /proc/sys/net/ipv4/tcp_window_scaling');
  echo ('/proc/sys/net/ipv4/tcp_window_scaling = ');echo shell_exec('cat /proc/sys/net/ipv4/tcp_window_scaling');
}

if(file_exists("/proc/sys/net/ipv4/tcp_sack")){
  shell_exec('echo "0" > /proc/sys/net/ipv4/tcp_sack');
  echo ('/proc/sys/net/ipv4/tcp_sack = ');echo shell_exec('cat /proc/sys/net/ipv4/tcp_sack');
  
}
	
	
}//ok no more tcp tunes for the kernel, hes cut off.

//lets show them iptables status if they ask for it
if($args == '--status'){
    $NUM_LINES = shell_exec("$IPTABLES -L -n | wc -l | awk '{print $1}'");
    echo shell_exec("$IPTABLES -L -n");
  // echo shell_exec("echo -e \"\033[32mphpMyFirewall - Running!\"");
  // echo shell_exec("echo -e -n \"\033[0m \"");

/*        
  if($NUM_LINES > "10"){
   echo shell_exec("echo -e \"\033[31mphpMyFirewall - Stopped!\"");
   echo shell_exec("echo -e -n \"\033[0m \"");
    }
  if($NUM_LINES != "11"){
   echo shell_exec("echo -e \"\033[32mphpMyFirewall - Running!\"");
   echo shell_exec("echo -e -n \"\033[0m \"");

    }

  
	echo -e "\033[31mphpMyFirewall - Stopped!"
	echo -e "\033[32mphpMyFirewall - Running!"
	echo -e -n "\033[0m "
*/
}
//stop the firewall
if($args == '--stop'){
    shell_exec("$IPTABLES -P INPUT ACCEPT");
    shell_exec("$IPTABLES -P OUTPUT ACCEPT");
    shell_exec("$IPTABLES -F");
    shell_exec("$IPTABLES -L -n");
    echo "\n\nFirewall - Stopped!\n\n";
}

?>
