# API Exercise
This is an exercise that creating an API inside Docker.

## Prerequisite
Since the API using Google distance API to calculate the distance between two points, please update the Google API key in 'Dockerfile'

## Kick start
A shell script is perpared for initial setup
```bash
./start.sh
```

## Api Interface
#### Place order

  - Method: `POST`
  - URL path: `/orders`
  - Request body:

    ```
    {
        "origin": ["START_LATITUDE", "START_LONGITUDE"],
        "destination": ["END_LATITUDE", "END_LONGITUDE"]
    }
    ```

  - Response:

    Header: `HTTP 200`
    Body:
      ```
      {
          "id": <order_id>,
          "distance": <total_distance>,
          "status": "UNASSIGNED"
      }
      ```
    or

    Header: `HTTP <HTTP_CODE>`
    Body:

      ```
      {
          "error": "ERROR_DESCRIPTION"
      }
      ```

  - Test:
      ```
      curl -X POST -H "Content-Type: application/json" -d '{"origin": ["40.6655101","-73.89188969999998"],"destination": ["40.6905615","-73.9976592"]}' "http://localhost:8080/orders"
      ```

#### Take order

  - Method: `PATCH`
  - URL path: `/orders/:id`
  - Request body:
    ```
    {
        "status": "TAKEN"
    }
    ```
  - Response:
    Header: `HTTP 200`
    Body:
      ```
      {
          "status": "SUCCESS"
      }
      ```
    or

    Header: `HTTP <HTTP_CODE>`
    Body:
      ```
      {
          "error": "ERROR_DESCRIPTION"
      }
      ```

  - Test:
      ```
      curl -X PATCH -H "Content-Type: application/json" -d '{"status": "TAKEN"}' "http://localhost:8080/orders/5ee8e6626c7ca"
      ```

#### Order list

  - Method: `GET`
  - Url path: `/orders?page=:page&limit=:limit`
  - Response:
    Header: `HTTP 200`
    Body:
      ```
      [
          {
              "id": <order_id>,
              "distance": <total_distance>,
              "status": <ORDER_STATUS>
          },
          ...
      ]
      ```

    or

    Header: `HTTP <HTTP_CODE>` Body:

    ```
    {
        "error": "ERROR_DESCRIPTION"
    }
    ```
    
  - Test:
      ```
      curl -X GET "http://localhost:8080/orders?limit=10&page=1"
      ```
