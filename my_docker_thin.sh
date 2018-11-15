#!/bin/bash
message="\nUsage ./my_docker_thin.sh [Source] [Target] [Base]\n
\n
\tExample : ./my_docker_thin.php gis_pdal_strip:0.01 gis_pdal_strip:0.02 docker.io/fedora:latest\n
\n  
"
if [ $# != "3" ];then 
  echo -e $message
  exit
fi

S_D=$1
T_D=$2
B_D=$3

backup_paths="etc usr var"

# t timestamp
t=$(date +%s)
tmp_path="/opt/${t}"
mkdir $tmp_path

#step 0 remove target
echo -e "\nStep 0 remove target ${T_D}...\n"
docker rmi ${T_D} --force

echo -e "\nStep 1 run backup...\n"
#CMD="docker run -v ${tmp_path}:/${t} ${S_D} cp -r ${backup_paths} /${t}"
#echo $CMD
docker run -v ${tmp_path}:/${t} ${S_D} cp -r ${backup_paths} /${t}

echo -e "\nStep 2 create empty base...\n"
docker tag ${B_D} ${T_D}

#step 3 copy 
echo -e "\nStep 3 copy to ${T_D}...\n"
docker run -v ${tmp_path}:/${t} ${T_D} /bin/bash -c "yes | cp -rf /${t}/* / && ldconfig "

#step 4 remove tmp
echo -e "\nStep 4 remove temp path ${tmp_path}...\n"
rm -fr ${tmp_path}

#step 5 commit ${T_D}
echo -e "\nStep 5 commit ${T_D}...\n"
IMAGE_ID=$(docker ps -a |head -n2|tail -n1|awk -F ' ' '{print $1}')
docker commit ${IMAGE_ID} ${T_D}

echo -e "\nDone...${T_D}\n"
echo -e "\n\n  docker images \n\n"
 