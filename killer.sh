#!/bin/bash

kill -9 `ps aux | grep dm.php | awk '{print $2}'`
kill -9 `ps aux | grep dos-child.php | awk '{print $2}'`