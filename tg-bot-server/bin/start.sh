mkdir -p ../data
nohup ./telegram-bot-api --api-id=23137454 --api-hash=73a4b06c6458e3252dc8818811e1153e --local -p 8081 -s 8082 -d ../data -t temp -l log.log -v 1 &
