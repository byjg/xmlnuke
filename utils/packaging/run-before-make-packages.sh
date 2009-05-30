#!/bin/sh

XMLNUKE="../.."

mkdir xmlnuke-common/doc-pak
cp $XMLNUKE/AUTHORS xmlnuke-common/doc-pak
cp $XMLNUKE/CONTRIBUTORS xmlnuke-common/doc-pak
cp $XMLNUKE/*README* xmlnuke-common/doc-pak
cp $XMLNUKE/LICENSE xmlnuke-common/doc-pak
cp $XMLNUKE/COPYING xmlnuke-common/doc-pak
cp $XMLNUKE/VERSION xmlnuke-common/doc-pak

