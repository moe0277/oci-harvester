#!/bin/sh
#
#

export COMPARTMENT_UUID="ocid1.compartment.oc1..update"
export SUBNET_UUID="ocid1.subnet.oc1.phx.update"
export IMAGE_UUID="ocid1.image.oc1.phx.update"
export SHAPE="BM.DenseIO.E4.128"

# update ad below
export AD="XXXX:PHX-AD-1"

export SSH_KEY_FILE="/root/.oci/ssh.pub" # public key for console

if [ -z "${1}" ]; then
	echo ""
	echo "Usage: launch.sh configfile"
	echo ""
	exit 1
fi
if [ ! -e ${1} ]; then
	echo ""
	echo "Config file: ${1}, not found"
	echo ""
	exit 1
fi

INSTANCE_ID=$(oci compute instance launch --subnet-id ${SUBNET_UUID} --shape ${SHAPE} --availability-domain ${AD} -c ${COMPARTMENT_UUID} --image-id ${IMAGE_UUID} --ipxe-script-file ${1} --query data.id --raw-output --wait-for-state RUNNING)
if [ "$?" -ne "0" ]; then
	exit 1
fi

echo "Waiting for system to settle..."
sleep 10
echo ""

# create console connection
oci compute instance-console-connection create --instance-id ${INSTANCE_ID} --ssh-public-key-file ${SSH_KEY_FILE} --wait-for-state ACTIVE

