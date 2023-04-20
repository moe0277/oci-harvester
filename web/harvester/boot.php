#!ipxe

<?php
function install() { 
	echo "set base_url http://ip-address/harvester
set build_version v1.1.2-rc7
set kernel_file harvester-\${build_version}-vmlinuz-amd64
set initrd_file harvester-\${build_version}-initrd-amd64
set rootfs_file harvester-\${build_version}-rootfs-amd64.squashfs
set kernel_url \${base_url}/\${kernel_file}
set initrd_url \${base_url}/\${initrd_file}
set rootfs_url \${base_url}/\${rootfs_file}
set cmdline initrd=\${initrd_file} ip=dhcp net.ifnames=1 rd.cos.disable rd.noverifyssl console=tty1 root=live:\${rootfs_url}
kernel \${kernel_url} \${cmdline}
initrd \${initrd_url}
boot\n";
}

function boot() {
	echo "set base_url http://ip-address/harvester
set build_version v1.1.2-rc7
set kernel_file harvester-\${build_version}-vmlinuz-amd64
set initrd_file harvester-\${build_version}-initrd-amd64
set rootfs_file harvester-\${build_version}-rootfs-amd64.squashfs
set kernel_url \${base_url}/\${kernel_file}
set initrd_url \${base_url}/\${initrd_file}
set rootfs_url \${base_url}/\${rootfs_file}
set cmdline initrd=\${initrd_file} console=tty1 root=LABEL=COS_ACTIVE cos-img/filename=/cOS/active.img panic=0 net.ifnames=1 rd.cos.oemtimeout=120 rd.cos.oemlabel=COS_OEM audit=1 audit_backlog_limt=8192 intel_iommu=on amd_iommu=on iommu=pt
kernel \${kernel_url} \${cmdline}
initrd \${initrd_url}
boot\n";	
}


$clientip = "";
try { 
	$clientip = $_SERVER['REMOTE_ADDR'];
} catch (Exception $e) {
	$clientip = "0.0.0.0"; 
} finally {
	if ( $clientip == "" ) { $clientip = "0.0.0.0"; }
}

echo "# Node IP: $clientip\n";
echo "#\n";

$mysqli = new mysqli('localhost', "root", "I love 0racle!", "harvester");
$result = $mysqli->query("SELECT * from nodes where ip='$clientip'");
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) { 
		if ($row['install'] ) { 
			install();	
		} 
		if (! $row['install']) {
			boot();
		}
	}
} else {
	// no record in db:
	// add record w/ boot for disk flag
	// run installer 
	$result = $mysqli->query("INSERT INTO nodes (ip, install) values ('$clientip', 0)");
	install();
}
$mysqli->close();
?>
