<?php


function to_decimal_ips($ips)
{

    if (!is_array($ips)) {
        return [];
    }

    foreach ($ips as $id => $ipval) {

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
    return $iparray;
}


function snmp_query($from_ip)
{

    $session = new SNMP(SNMP::VERSION_2c, $from_ip, "public");
//    echo "Sprawdzam adres: " . $from_ip . "\n";

    $hex_ips = $session->walk("1.3.6.1.4.1.9.9.23.1.2.1.1.4", TRUE, 1);
    $error = $session->getErrno() == SNMP::ERRNO_TIMEOUT;
    if ($error == 1) {
        return [];
    }
    $ips = to_decimal_ips($hex_ips);
//    foreach ($ips as $k => $v) {
//        echo sprintf("\tSNMP ID: %s - IP: %s\n", $k, $v);
//
//    }
    $ports = $session->walk("1.3.6.1.4.1.9.9.23.1.2.1.1.7", TRUE, 1);
    $error = $session->getErrno() == SNMP::ERRNO_TIMEOUT;
    if ($error == 1) {
        return [];
    }

    $resp = [];
    foreach ($ips as $oid_key => $ip) {
        $resp[] = [$ip => $ports[$oid_key]];
    }
    return $resp;
}


$VISITED_IPS = [];

function gn($switch_ip)
{
    global $VISITED_IPS;
    if (!array_key_exists($switch_ip, $VISITED_IPS)) {
        $VISITED_IPS[$switch_ip] = [];
    }
    $nb = snmp_query($switch_ip);
    $nb = is_array($nb) ? $nb : [];

    foreach ($nb as $nth) {
        $dest_ip = array_keys($nth)[0];
        $via_port = $nth[$dest_ip];
        $VISITED_IPS[$switch_ip][$dest_ip] = $via_port;
        if (!array_key_exists($dest_ip, $VISITED_IPS)) {

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

//gn("10.5.255.1");
gn($argv[1]);
//var_dump($VISITED_IPS);
