<?php
//phpMyFireWall
//author dstjohn@mediacast1.com

################################################################################
//configuration
//set system ips
//$SERVER_IPS 0.0.0.0/0 = any destination, if you want to limit access per ip
//please use real ips and do not use 0.0.0.0/0
//for more then one ip please seperate each ip with the spacebar, no tabs!
$SERVER_IPS = "0.0.0.0/0";

//$MASTER_IP 0.0.0.0/0 = any ip, please set this to the main server ip address
//do not use 0.0.0.0/0 unless you want to allow certain features on all ip address's
//on this machine. This should be set to one ip address only
$MASTER_IP = "0.0.0.0/0";

//Trusted ip address's, these are ips that are allowed to ping and traceroute to your server
//if you have a monitoring service, enter in the server ips here.
//for more then one ip please seperate each ip with the spacebar, no tabs!
//example $TRUSTED_IPS = "216.77.77.8 216.12.5.1 216.1.4.5";
$TRUSTED_IPS = "0.0.0.0/0";

################################################################################
//paths
$SYSPATHS = "/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin";
$THISDIR  = "/usr/local/phpmyfirewall"; //directory phpMyFireWall is installed to
$IPTABLES = "/sbin/iptables";             //path to iptables binary
$MODPROBE = "/sbin/modprobe";             //path to modprobe binary


################################################################################
//ports settings/port db files
//system ports
$TCPINGRESSDB = "./ports/tcpingress.db";
$TCPEGRESSDB = "./ports/tcpegress.db";
$UDPINGRESSDB = "./ports/udpingress.db";
$UDPEGRESSDB = "./ports/udpegress.db";
/*
commin ports to keep open
PORT      STATE SERVICE
25/tcp    open  smtp
53/tcp    open  domain
88/tcp    open  kerberos-sec
110/tcp   open  pop3
143/tcp   open  imap
783/tcp   open  spamassassin
873/tcp   open  rsync
953/tcp   open  rndc
993/tcp   open  imaps
995/tcp   open  pop3s
2401/tcp  open  cvspserver
3306/tcp  open  mysql
10000/tcp open  snet-sensor-mgmt

*/
$SYS_TCP_IN_PORTS  = "20 21 22 25 43 53 80 110 143 443 783 873 993 995 2401 3306";
$SYS_TCP_OUT_PORTS = "20 21 22 25 43 53 80 110 143 443 783 873 993 995 2401 3306";
$SYS_UDP_IN_PORTS  = "22 53 161 162";
$SYS_UDP_OUT_PORTS = "22 53 161 162";

//custom ports
$CUST_TCP_IN_PORTS  = "20 21 22 10000 20000";
$CUST_TCP_OUT_PORTS = "20 21 22 10000 20000";
$CUST_UDP_IN_PORTS  = "20 21 22 10000 20000";
$CUST_UDP_OUT_PORTS = "20 21 22 10000 20000";



//ban list (should be path to file name)
// you can add ips in this file, seperated by new lines
// example
// 216.22.22.2
// 255.3.3.2
$BLACKLIST = "$THISDIR/blacklist/blocked.ips";

//trusted ports to allow from trusted ips assigned above
$TCP_IN_TRUSTED = "22 10000 20000";

//misc
$PRIVPORTS   = "0:1024";
$UNPRIVPORTS = "1025:65535";
###############################################################################
// misc ip settings
$LOOPBACK 				= "127.0.0.0/8";
$CLASS_A 				= "10.0.0.0/8";
$CLASS_B 				= "172.16.0.0/16";
$CLASS_C 				= "192.168.0.0/24";
$CLASS_D_MULTICAST 		= "224.0.0.0/4";
$CLASS_E_RESERVED_NET 	= "240.0.0.0/4";
$BROADCAST_SRC 			= "0.0.0.0";
$BROADCAST_DEST 		= "255.255.255.255";
//interface we will be filtering traffic on, comma delimit virtual interfaces
$ROOT_INTERFACE 	= "eth0";
$VIRTUAL_INTERFACES = "eth0:0";
//$NUM_VIRT_INTERFACES corelates with the number of virtual interfaces above, do not count lo interface
$NUM_VIRT_INTERFACES = "0"; 

###############################################################################
//set some system permissions
//allow outgoing traceroute
$TRACE_OUT = "OFF"; //ON = allow outgoing traceroute, OFF = deny outgoing traceroute
$PING_OUT  = "OFF"; //ON = allow outgoing ping, OFF = deny outgoing ping
$USE_IDENT = "OFF"; //ON = allow ident port 113, OFF = deny ident port 113
$PINGIN    = "ON";  //ON = block all incoming icmp, OFF = allow icmp incoming packets

// THIS SLOWS DOWN WEB PAGE LOADS DRAMATICALLY!!! I.E. APACHE/HTTP PROTOCOL
// Only enable this  if you find that you are the victim of a syn-flood
// attack!
$SYN_FLOOD_PROTECTION = "ON"; //ON = syn flood protection on, OFF = syn flood protection off

//logging to kernel
$LOGGING = "ON"; //ON = logging to kernel on, dmesg. OFF = no logging
$LOG_EVENT_PER_MINUTE = "25"; //set this to how many events you want to log per minute, is using logging.

###############################################################################

?>

