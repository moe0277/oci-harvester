# oci-harvester
## OCI relevant code for Harvester

### NOTE: Tested with Harvester v1.1.2-rc7

OCI allows you to run bare-metal servers. Harvester can be deployed on bare-metal servers in OCI. There are two types of bare-metal servers in OCI: standard and dense i/o. 
So far, we have been able to deploy to dense i/o servers - because they provide local disks where Harvester can be installed. 
We have not been able to install Harvester to a diskless system where the boot volume is an iSCSI lun delivered over the network - this is required for OCI bare-metal hosts without dense i/o. 

The high level pattern for installing Harvester on OCI is:

### 1. Deploy a VCN and at least 1 subnet in OCI. 

  a. Ensure the subnet's security list allows traffic on all ports within the subnet. 
  
  b. For eg if the subnet cidr is 10.0.10.0/24 - configure the security list to allow all traffic/ports for 10.0.10.0/24. 
  
### 2. Deploy a small (Oracle Linux) server w/ 1 OCPU and 4 GB RAM, default disk size. 

  a. install oci-cli, apache, mysql, and php on this server because it will be hosting Harvester files. 
  
  b. ensure apache can execute php scripts. 
  
  c. deploy the boot.php file in the web/ folder to for example /var/www/html/harvester/ (for apache on rpm-based linux systems).  
  
  d. modify the boot.php script to ensure the path and ips are correct in the boot() and install() functions.
  
  e. modify the boot.php script to ensure correct db information. 
  
  f. you can also modify boot.php for the install() section to include an automated install yaml file. 
  
  g. modify the launch.sh script to update the variables for cloud resources (compartment, subnet, image, ssh key, etc). 
  
  h. modify the hsimple.cfg ipxe script to ensure the path and ip is correct for the web server and location of boot.php. 
  
  i. Configure MySQL db server, create a blank database and load the harvester.mysql file in the db/ folder into it. 
  
    i. this creates a single table named nodes with 3 columns: id (auto incremented), ip (ip address of connecting booting host), and install (run install()? 1 = true, 0 = false, run boot())
  
### 3. Ensure you have quota in your tenancy for the DenseIO BM Shape you want to use. 

### 4. Launch an instance using the launch script: ./launch.sh hsimple.cfg

NOTE: In OCI, the same ipxe script used to launch an instance is ran each boot. 

### 5. Boot sequence:

  a. first boot: 

    i. when invokved via launch.sh - a bare metal dense i/o host will boot and run the hsimple.cfg ipxe script - which will redirect it to the webserver hosted boot.php to fetch and execute more ipxe code. 

    ii. for a new instance, boot.php will create a new record in the mysql database, and it will instruct the new host to boot to harvester installer the code is in boot.php's install() function.   

    iii. the launch script also creates a console connection. use this to view the local console of the booting host. 

    iv. via local console: install harvester to the 1st listed NVME disk. 

    v. after installation, the harvester installer will reboot. 

  b. second and subsequent boots:

    i. boot.php will find an existing record in the mysql database for this host and use the boot() function to boot from the NVME disk where harvester was installed on first boot. 

    ii. if you want to force a reinstall, update the database record for your host with an install flag of 1. 1=install 0=boot. 


