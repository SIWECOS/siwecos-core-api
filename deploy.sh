!#/bin/bash
docker build . -t siwecos/siwecos-core-api:dev
docker push siwecos/siwecos-core-api:dev
kubectl patch deployment siwecos-core-api -p \
  "{\"spec\":{\"template\":{\"metadata\":{\"labels\":{\"date\":\"`date +'%s'`\"}}}}}"