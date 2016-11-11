#!/usr/bin/env python
# -*- coding: utf-8 -*-






def query0(from_ip):
    if from_ip == ":1":
        return [(":2", "/1"), (":3", "/3")]
    if from_ip == ":2":
        return [(":1", "/4"), (":4", "/1"), (":5", "/2"), (":3", "/3")]
    if from_ip == ":3":
        return [(":2", "/1"), (":1", "/2")]
    if from_ip == ":4":
        return [(":2", "/1")]
    if from_ip == ":5":
        return [(":2", "/1")]


def query1(from_ip):
    if from_ip == ":1":
        return [(":2", "/0"), (":3", "1")]
    if from_ip == ":2":
        return [(":1", "/0"), (":1", "/1")]
    if from_ip == ":3":
        return [(":1", "/0")]


snmp_query = query0

VISITED_IPS = {}


def gn(switch_ip):
    if not VISITED_IPS.has_key(switch_ip):
        VISITED_IPS[switch_ip] = {}
    nb = snmp_query(switch_ip)
    for nth in nb:
        dest_ip = nth[0]
        via_port = nth[1]
        VISITED_IPS[switch_ip][dest_ip] = via_port
        if not VISITED_IPS.has_key(dest_ip):
            gn(dest_ip)


if __name__ == '__main__':
    gn(":1")
    print VISITED_IPS

    # tr = Tree()
    # s1 = TreeNode((1, {":2": "/0", ":3": "/1"}))
    # s2 = TreeNode((2, {":1": "/0", ":1": "/1"}))
    # s3 = TreeNode((3, {":1": "/0"}))
    #
    # gn(":1")
    # gn(":2")


    # r2 = TreeNode("0")
    # r3 = TreeNode("1")
    #
    # n4 = TreeNode("D")
    # n5 = TreeNode("E")
    # n6 = TreeNode("F")
    #
    # root.add_child(n2)
    # root.add_child(n3)
    # n2.add_child(n4)
    # n2.add_child(n5)
    # n3.add_child(n6)

    # print_tree(root)
    # mark_leaf_nodes_from(root)
    # print LEAFS
