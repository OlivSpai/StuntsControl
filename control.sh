#!/bin/sh

case "$1" in
    start)
		cd /home/StuntsControl
		php control.php TM2 </dev/null >StuntsControl.log 2>&1 &
	
		echo $! > /var/run/StuntsControl.pid
		echo StuntersControl started
    ;;
    
	stop)
		kill -TERM `cat /var/run/StuntsControl.pid`
		echo StuntersControl stopped
    ;;
	*)
		echo "Usage: ${0} {start|stop}"
esac
