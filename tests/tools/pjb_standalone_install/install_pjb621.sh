#!/bin/sh
BASEDIR=$(dirname $0) 
INSTALL_DIR=$BASEDIR/pjb621
URL="http://downloads.sourceforge.net/project/php-java-bridge/Binary%20package/php-java-bridge_6.2.1/JavaBridgeTemplate621.war?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fphp-java-bridge%2Ffiles%2FBinary%2520package%2Fphp-java-bridge_6.2.1%2F&ts=1415114437&use_mirror=softlayer-ams"
FILE=$INSTALL_DIR/JavaBridgeTemplate.war
if [ ! -f $FILE ]; then
    mkdir -p $INSTALL_DIR
    wget $URL -O $FILE;
    unzip $FILE -d $INSTALL_DIR;
fi