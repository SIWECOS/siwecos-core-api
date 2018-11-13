!#/bin/bash
docker build . -t siwecos/siwecos-core-api:staging
docker push siwecos/siwecos-core-api:staging
kubectl patch deployment siwecos-core-api-staging -p \
  "{\"spec\":{\"template\":{\"metadata\":{\"labels\":{\"date\":\"`date +'%s'`\"}}}}}"