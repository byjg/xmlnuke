#!/bin/sh

svn log $1 | grep -v -e "^Modified\ \:" | grep -v -e "^Added\ \:" | grep -v -e "^Deleted\ \:" 
