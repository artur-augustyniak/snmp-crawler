#!/usr/bin/env python
# -*- coding: utf-8 -*-


## TO JEST TYLKO DO RYSOWANIA DRZEWKA
## http://stackoverflow.com/a/30893896

def print_tree(current_node, indent="", last='updown'):
    nb_children = lambda node: sum(nb_children(child) for child in node.children) + 1
    size_branch = {child: nb_children(child) for child in current_node.children}

    """ Creation of balanced lists for "up" branch and "down" branch. """
    up = sorted(current_node.children, key=lambda node: nb_children(node))
    down = []
    while up and sum(size_branch[node] for node in down) < sum(size_branch[node] for node in up):
        down.append(up.pop())

    """ Printing of "up" branch. """
    for child in up:
        next_last = 'up' if up.index(child) is 0 else ''
        next_indent = '{0}{1}{2}'.format(indent, ' ' if 'up' in last else '│', " " * len(current_node.data))
        print_tree(child, indent=next_indent, last=next_last)

    """ Printing of current node. """
    if last == 'up':
        start_shape = '┌'
    elif last == 'down':
        start_shape = '└'
    elif last == 'updown':
        start_shape = ' '
    else:
        start_shape = '├'

    if up:
        end_shape = '┤'
    elif down:
        end_shape = '┐'
    else:
        end_shape = ''

    print '{0}{1}{2}{3}'.format(indent, start_shape, current_node.data, end_shape)

    """ Printing of "down" branch. """
    for child in down:
        next_last = 'down' if down.index(child) is len(down) - 1 else ''
        next_indent = '{0}{1}{2}'.format(indent, ' ' if 'down' in last else '│', " " * len(current_node.data))
        print_tree(child, indent=next_indent, last=next_last)


## TU JEST CAŁY PROBLEM
## http://stackoverflow.com/a/30893896


LEAFS = []


def mark_leaf_nodes_from(node):
    if len(node.children) == 0:
        LEAFS.append(node)
    else:
        for n in node.children:
            mark_leaf_nodes_from(n)


class TreeNode(object):
    def __init__(self, data):
        self.data = data
        self.children = []

    def add_child(self, obj):
        self.children.append(obj)

    def __str__(self):
        return self.data

    def __repr__(self):
        return self.__str__()


if __name__ == '__main__':
    # tr = Tree()
    root = TreeNode("ROOT")
    n2 = TreeNode("B")
    n3 = TreeNode("C")
    n4 = TreeNode("D")
    n5 = TreeNode("E")
    n6 = TreeNode("F")

    root.add_child(n2)
    root.add_child(n3)
    n2.add_child(n4)
    n2.add_child(n5)
    n3.add_child(n6)

    print_tree(root)
    mark_leaf_nodes_from(root)
    print LEAFS
