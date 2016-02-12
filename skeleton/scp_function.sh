function scp___FUNCTION_NAME__() {
    echo "**************************************"
    echo "** SERVER     : __HOSTNAME__"
    echo "** USERNAME   : __USERNAME__"
    echo "** PASS       : __PASSWORD__"
    echo "**************************************"

    scp $1 __USERNAME__@__HOSTNAME__:$2
}

