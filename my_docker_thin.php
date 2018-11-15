<?php
# *******************************************************
# * Author: FeatherMountain                               
# * Github: https://github.com/shadowjohn/my_docker_thin 
# * Date: 2018-11-15                    
# * Version: 0.1
# *             
# *******************************************************
function my_system($cmd)
{
  $fp=popen($cmd,"r");
  while (!feof($fp)) {
    $buffer = fgets($fp, 4096);
    echo $buffer;
  }
  pclose($fp);
}
$message = "
Usage php my_docker_thin.php [Source] [Target] [Base]

  Example : php my_docker_thin.php gis_pdal_strip:0.01 gis_pdal_strip:0.02 docker.io/fedora:latest

";
  
  if($argc!=4)
  {
    echo $message;
    exit();
  }
  $S_D = $argv[1];
  $T_D = $argv[2];
  $B_D = $argv[3];

  $backup_paths = [
    "etc",
    "usr",
    "var"
  ];
  $mbp = implode(" ",$backup_paths);
  $t = time();
  $tmp_path = "/opt/{$t}";
  mkdir($tmp_path,true);

  #step 0 remove target
  echo "\nStep 0 remove target {$T_D}...\n";
  $CMD = "docker rmi {$T_D} --force";
  my_system($CMD);

  #step 1 run backup
  echo "\nStep 1 run backup...\n";
  $CMD = "docker run -v {$tmp_path}:/{$t} {$S_D} cp -r {$mbp} /{$t}";
  my_system($CMD);

  #step 2 create empty base
  echo "\nStep 2 create empty base...\n";
  $CMD = "docker tag {$B_D} {$T_D}";
  my_system($CMD);

  #step 3 copy 
  echo "\nStep 3 copy to {$T_D}...\n";
  $CMD = "docker run -v {$tmp_path}:/{$t} {$T_D} /bin/bash -c \"yes | cp -rf /{$t}/* / && ldconfig \"";
  my_system($CMD);

  #step 4 remove tmp
  echo "\nStep 4 remove temp path {$tmp_path}...\n";
  $CMD = "rm -fr {$tmp_path}";
  my_system($CMD);

  #step 5 commit {$T_D}
  echo "\nStep 5 commit {$T_D}...\n";
  $CMD = 'docker ps -a |head -n2|tail -n1|awk -F \' \' \'{print $1}\'';
  $IMAGE_ID = trim(`{$CMD}`);
  $CMD = "docker commit {$IMAGE_ID} {$T_D}";
  my_system($CMD);

  echo "\nDone...{$T_D}\n";
  echo "\n\n  docker images \n\n";


