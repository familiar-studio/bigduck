FROM circleci/node:7.10
RUN apt-get update && apt-get install -y rsync
