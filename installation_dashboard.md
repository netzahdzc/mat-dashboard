## Installation 

Follow below directions to install this container:

1. Clone the repository
2. Run `docker build . -t mat-dashboard`
3. Run `docker-compose up` to wire required services

### Testing

1. Run with: `docker run -d --restart=always -p 8080:80 [CONTAINER ID]`

2. Get into the component with: `docker exec -i -t [CONTAINER ID] /bin/bash`

## Troubles?

Get in touch [netzahdzc@cicese.mx (mailto:netzahdzc@cicese.mx)