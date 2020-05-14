A place for Traceurs to share spots and challenges.

Setup:
Run docker-compose build
Run docker-compose up -d
Run docker ps
Run docker exec -it {mysql id} /bin/bash
Run mysql -u root -p
Enter password
Run "ALTER USER 'parkourhub' IDENTIFIED WITH mysql_native_password BY '{password}';
Exit mysql container
Run ./dartisan migrate
Run ./dartisan storage:link
