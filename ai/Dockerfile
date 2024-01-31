FROM python:3.13.0a2-alpine3.19

COPY . .

RUN pip install --no-cache-dir -r requirements.txt

CMD ["flask", "--app", "app" , "run", "--host", "0.0.0.0", "--debug"]

EXPOSE 5000
