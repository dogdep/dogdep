FROM node:0.10.38
MAINTAINER DataDog <info@datadog.com>

VOLUME /app
WORKDIR /app

RUN curl -L https://get.docker.com/builds/Linux/i386/docker-latest > /usr/bin/docker && \
    chmod +x /usr/bin/docker

EXPOSE 4000

CMD ["sh", "/app/run.sh"]
