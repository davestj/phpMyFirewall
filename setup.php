#!/usr/bin/php -q
<?php
//phpMyFireWall
//author dstjohn@mediacast1.com
error_reporting(E_ERROR);
include('./src/etc/config.php');
//set some env vars
putenv("PATH=$SYSPATHS");
//get kernel version
$uname = shell_exec("uname -r");
$reuname = trim($uname);

//make sure root is running this app
$whoru = shell_exec("whoami");
$rewho = trim($whoru);


//parse command line arguments
$args = trim(next($HTTP_SERVER_VARS["argv"]));

if($args == '' || $args == '-h' || $args == '--help'){
echo "----------------------------------------------------------------------\n
	  $argv[0] usage:\n
					$argv[0] --install (installs phpMyFirewall)\n
					$argv[0] --check (checks system for a compatable install)\n
					$argv[0] --uninstall (uninstalls phpMyFirewall)\n\n";
}



//lets run through the system and make sure phpMyFirewall will even work
if($args == '--check'){

//check and make sure user root is executing this script
if(eregi('root',$rewho)){
		echo "$argv[0] user check: $rewho";
		echo shell_exec("echo -e \"\033[32m			[PASSED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		$whou = TRUE;
}else{
		echo "\n$argv[0] usercheck:";
		echo shell_exec("echo -e \"\033[31m			[FAILED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		echo "$argv[0] must be ran as root, you are $USER, exiting!\n";
		
}
//run kernel version check
	if(eregi('2.6',$reuname)){
		echo "kernel check: $reuname";
		echo shell_exec("echo -e \"\033[32m			[PASSED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		$kern = TRUE;
		
	}else{
		echo "kernel check";
		echo shell_exec("echo -e \"\033[31m			[FAILED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		echo "YOU ARE RUNNING AN OLDER KERNEL,\nPHPMYFIREWALL WILL NOT WORK WITH KERNELS\nOTHER THEN THE 2.6 BRANCH, exiting!\n";
		
	}

//check iptables and modprobe
if(file_exists($IPTABLES)){
//get iptables version
$iptbname = shell_exec("$IPTABLES --version");
$reiptbname = trim($iptbname);
	
		echo "iptables check: $reiptbname";
		echo shell_exec("echo -e \"\033[32m			[PASSED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		$tables = TRUE;
}else{
		echo "iptables check: /sbin/iptables --version";
		echo shell_exec("echo -e \"\033[31m			[FAILED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		echo "iptables was not found,please edit src/etc/config.php\nand enter the exact location of iptables\nexiting!\n";
		
}

//check the existance of modprobe
if(file_exists($MODPROBE)){
//get modprobe ver
$modprobname = shell_exec("$MODPROBE --version");
$remodprobname = trim($modprobname);
		echo "modprobe check: $remodprobname";
		echo shell_exec("echo -e \"\033[32m			[PASSED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		$probe = TRUE;

}else{
		echo "modprobe check: /sbin/modprobe --version";
		echo shell_exec("echo -e \"\033[31m			[FAILED]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		echo "modprobe was not found, please edit src/etc/config.php\nand enter the exact location of modprobe\nexiting!\n";
		
}

if($probe == 'TRUE' && $tables == 'TRUE' && $kern == 'TRUE' && $whou == 'TRUE'){
$writ = "OK";
$fp = fopen("STATUS", 'w');
fwrite($fp,$writ);
fclose($fp);

}else{
$writ2 = "FALSE";
$fp2 = fopen("STATUS", 'w');
fwrite($fp2,$writ2);
fclose($fp2);	
}


}

if($args == '--install'){
$fp = fopen('./STATUS','r');
$cont = fread($fp,1024);
fclose($fp);

 if($cont == 'OK'){
 		echo shell_exec("echo -e \"\033[36m[PREPARING FOR phpMyFirewall installation, please standby]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
		echo shell_exec("echo -e \"\033[36m\"");
		echo shell_exec("mkdir /usr/local/phpmyfirewall");
		echo shell_exec("cp -r ./src/* /usr/local/phpmyfirewall/");
		echo shell_exec("chown -R root.root /usr/local/phpmyfirewall/; chmod 755 /usr/local/phpmyfirewall/*.php; chmod 0700 /usr/local/phpmyfirewall; ls -l /usr/local");
		echo shell_exec("echo \"\n\n\";cat ./SETUP; sleep 4");
		echo shell_exec("echo \"\n\n\"; echo \"INSTALLATION COMPLETE\"");
		echo shell_exec("echo -e -n \"\033[0m \"");
 	
 }else{
		echo shell_exec("echo -e \"\033[31m[CANNOT INSTALL, please run [$argv[0] --check], ABORTING installation]!\"");
		echo shell_exec("echo -e -n \"\033[0m \"");	
 }
	
}



if($args == '--uninstall'){
		echo shell_exec("echo -e \"\033[31m[un-installing, PLEASE STAND BY]!\"");
		echo shell_exec("/usr/local/phpmyfirewall/firewall.php --stop");
		echo shell_exec("rm -rf /usr/local/phpmyfirewall; rm -rf ./STATUS; ls -l /usr/local");
		echo shell_exec("echo -e -n \"\033[0m \"");	

	
}
?>