FROM datadoglt/php56
MAINTAINER "DataDog" <info@datadog.lt>

ENV COMPOSER_VERSION "1.3.0rc1"

# Install git
RUN yum install -y git tar && \
    yum clean all

# Install docker-compose
RUN curl -L https://github.com/docker/compose/releases/download/$COMPOSER_VERSION/docker-compose-`uname -s`-`uname -m` > /usr/bin/docker-compose && \
    chmod +x /usr/bin/docker-compose
