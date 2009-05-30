#!/bin/sh

BASE=`basename $1 .pt-br.xml`

mv $BASE.pt-br.xml $BASE.en-us.xml
