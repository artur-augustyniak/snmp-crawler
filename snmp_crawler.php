<?php


$DEV_TYPES = [

    0x400 => 'Two-Port MAC Relay.',
    0x200 => 'CAST Phone Port / CVTA / Supports-STP-Dispute depending upon platform.',
    0x100 => 'Remotely-Managed Device.',
    0x80 => 'VoIP Phone.',
    0x40 => 'Provides level 1 functionality.',
    0x20 => 'The bridge or switch does not forward IGMP Report packets on non router ports.',
    0x10 => 'Sends and receives packets for at least one network layer protocol. If the device is routing the protocol, this bit should not be set.',
    0x08 => 'Performs level 2 switching. The difference between this bit and bit 0x02 is that a switch does not run the Spanning-Tree Protocol. This device is assumed to be deployed in a physical loop-free topology.',
    0x04 => 'Performs level 2 source-route bridging. A source-route bridge would set both this bit and bit 0x02.',
    0x02 => 'Performs level 2 transparent bridging.',
    0x01 => 'Performs level 3 routing for at least one network layer protocol.'

];


$link;
$scan_timestamp = time();

$insert_tpl = "INSERT
        INTO device(
        scan_timestamp,
        dev_ip,
        hw_parent_port_name,
        dev_name,
        dev_type_hex,
        dev_type_name,
        found_by_protocol,
        reached_from_dev_id)
        VALUES (
          '%s', '%s','%s','%s','%s','%s','%s', %s);";


$select_tpl = "SELECT id FROM device WHERE dev_ip = '%s'";

if (!$link = mysql_connect('localhost', 'przemekm', 'przemekm')) {
    echo 'Nie można nawiązać połączenia z bazą danych';
    exit;
}

if (!mysql_select_db('przemekm', $link)) {
    echo 'Nie można wybrać bazy danych';
    exit;
}


function to_decimal_ips($ips)
{

    if (!is_array($ips)) {
        return [];
    }

    foreach ($ips as $id => $ipval) {
        if ("" != $ipval) {
            $a = str_replace('Hex-STRING: ', '', $ipval);
            $string = str_replace(' ', '', $a);
            $arr1 = str_split($string, 2);

            $c = array();


            for ($i = 0; $i <= 3; $i++) {
                array_push($c, hexdec($arr1[$i]));
            }
            $ready = implode('.', $c);
            $iparray[$id] = $ready;
        }
    }
    return $iparray;
}


function dev_types_conv($dev_types)
{
    if (!is_array($dev_types)) {
        return [];
    }


    $ret_arr = [];

    foreach ($dev_types as $id => $dev_type) {
        if ("" != $dev_type) {
            $a = str_replace('Hex-STRING: ', '', $dev_type);
            $string = str_replace(' ', '', $a);

            $ret_arr[$id] = hexdec($string);
        }
    }
    return $ret_arr;
}


function match_dev_class($hexname)
{

    global $DEV_TYPE;

    foreach ($DEV_TYPE AS $mask => $desc) {

        if ($mask & $hexname) {
            return [$mask, $desc];
        }

    }
    return [0x0, ""];
}


function snmp_query($from_ip)
{

    $session = new SNMP(SNMP::VERSION_2c, $from_ip, "public");

    $hex_ips = $session->walk("1.3.6.1.4.1.9.9.23.1.2.1.1.4", TRUE, 1);
    $error = $session->getErrno() == SNMP::ERRNO_TIMEOUT;
    if ($error == 1) {
        return [];
    }
    $ips = to_decimal_ips($hex_ips);
    $ports = $session->walk("1.3.6.1.4.1.9.9.23.1.2.1.1.7", TRUE, 1);
    $error = $session->getErrno() == SNMP::ERRNO_TIMEOUT;
    if ($error == 1) {
        return [];
    }
    $dev_types = $session->walk("1.3.6.1.4.1.9.9.23.1.2.1.1.9", TRUE, 1);
    $error = $session->getErrno() == SNMP::ERRNO_TIMEOUT;
    if ($error == 1) {
        return [];
    }

    $dev_types = dev_types_conv($dev_types);

    $dev_names = $session->walk("1.3.6.1.4.1.9.9.23.1.2.1.1.6", TRUE, 1);
    $error = $session->getErrno() == SNMP::ERRNO_TIMEOUT;
    if ($error == 1) {
        return [];
    }


    $resp = [];
    foreach ($ips as $oid_key => $ip) {
        if ("0.0.0.0" != $ip) {

            $class_map = match_dev_class($dev_types[$oid_key]);
            $resp[] = [
                $ip => $ports[$oid_key],
                'dev_type_hex' => $class_map[0],
                'dev_name' => $dev_names[$oid_key],
                'dev_type_name' => $class_map[1],
                'protocol' => 'CDP'
            ];

        }
    }
    return $resp;
}


$VISITED_IPS = [];

function gn($switch_ip)
{
    global $VISITED_IPS;
    global $select_tpl;
    global $insert_tpl;
    global $link;
    global $scan_timestamp;
    if (!array_key_exists($switch_ip, $VISITED_IPS)) {
        $VISITED_IPS[$switch_ip] = [];
    }
    $nb = snmp_query($switch_ip);
    $nb = is_array($nb) ? $nb : [];

    foreach ($nb as $nth) {
        $dest_ip = array_keys($nth)[0];
        $via_port = $nth[$dest_ip];
        $protocol = $nth['protocol'];
        $dev_type_hex = $nth['dev_type_hex'];
        $dev_name = $nth['dev_name'];
        $dev_type_name = $nth['dev_type_name'];
        $
        $VISITED_IPS[$switch_ip][$dest_ip] = $via_port;
        if (!array_key_exists($dest_ip, $VISITED_IPS)) {
            $sql = sprintf($select_tpl, $switch_ip);
            $result = mysql_query($sql);
            $parent_id = mysql_fetch_object($result);
            //if(NULL == $parent_id) PRZERWIJ
            $q = sprintf($insert_tpl,
                $scan_timestamp,
                $dest_ip,
                $via_port,
                $dev_name,
                $dev_type_hex,
                $dev_type_name,
                $protocol,
                $parent_id->id
            );
            var_dump($q);
            mysql_query($q, $link);
            foreach ($VISITED_IPS as $node_ip => $net_env) {
                echo sprintf("NODE IP: %s see:\n", $node_ip);
                foreach ($net_env as $child_ip => $via_port) {
                    echo sprintf("\tCHILD IP: %s via PORT: %s\n", $child_ip, $via_port);
                }

            }
            gn($dest_ip);
        }
    }

}

$root_ip = $argv[1];


$q = sprintf($insert_tpl,
    $scan_timestamp,
    $root_ip,
    'ROOT',
    'ROOT',
    '0x000',
    'ROOT',
    'ROOT',
    'NULL'
);
var_dump($q);
mysql_query($q, $link);
gn($root_ip);
