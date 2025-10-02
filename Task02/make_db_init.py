import csv
import os
import re

def generate_sql_script():
    sql_commands = []
    
    sql_commands.append("DROP TABLE IF EXISTS movies;")
    sql_commands.append("DROP TABLE IF EXISTS ratings;")
    sql_commands.append("DROP TABLE IF EXISTS tags;")
    sql_commands.append("DROP TABLE IF EXISTS users;")
    sql_commands.append("")
    
    sql_commands.append('''CREATE TABLE movies (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    year INTEGER,
    genres TEXT NOT NULL
);''')
    
    sql_commands.append('''CREATE TABLE ratings (
    id INTEGER PRIMARY KEY,
    user_id INTEGER NOT NULL,
    movie_id INTEGER NOT NULL,
    rating INTEGER NOT NULL,
    timestamp INTEGER NOT NULL
);''')
    
    sql_commands.append('''CREATE TABLE tags (
    id INTEGER PRIMARY KEY,
    user_id INTEGER NOT NULL,
    movie_id INTEGER NOT NULL,
    tag TEXT,
    timestamp INTEGER NOT NULL
);''')
    
    sql_commands.append('''CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    gender TEXT NOT NULL,
    register_date DATE NOT NULL,
    occupation TEXT NOT NULL
);''')
    
    sql_commands.append("")
    
    with open('movies.csv', mode='r', encoding='utf-8') as file:  
        csv_reader = csv.reader(file)
        next(csv_reader)
        for row in csv_reader:
            year = re.search(r'\((\d{4})\)$', row[1])
            if year:
                year = year.group(1)
            else:
                year = 'NULL'
            clean_title = re.sub(r'\s*\(\d{4}\)$', '', row[1])
            title = clean_title.replace("'", "''")
            genres = row[2].replace("'", "''")
            sql_commands.append(f"INSERT INTO movies (id, title, year, genres) VALUES ({row[0]}, '{title}', {year}, '{genres}');")
    
    sql_commands.append("")
    
    with open('ratings.csv', mode='r', encoding='utf-8') as file:  
        csv_reader = csv.reader(file)
        next(csv_reader)
        for row in csv_reader:
            sql_commands.append(f"INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ({row[0]}, {row[1]}, {row[2]}, {row[3]});")
    
    sql_commands.append("")
    
    with open('tags.csv', mode='r', encoding='utf-8') as file:  
        csv_reader = csv.reader(file)
        next(csv_reader)
        for row in csv_reader:
            tag = row[2].replace("'", "''") if row[2] else ''
            sql_commands.append(f"INSERT INTO tags (user_id, movie_id, tag, timestamp) VALUES ({row[0]}, {row[1]}, '{tag}', {row[3]});")
    
    sql_commands.append("")
    
    with open('users.txt', mode='r', encoding='utf-8') as file:
        csv_reader = csv.reader(file, delimiter='|')
        for row in csv_reader:
            name = row[1].replace("'", "''")
            email = row[2].replace("'", "''")
            occupation = row[5].replace("'", "''")
            sql_commands.append(f"INSERT INTO users (id, name, email, gender, register_date, occupation) VALUES ({row[0]}, '{name}', '{email}', '{row[3]}', '{row[4]}', '{occupation}');")
    
    return sql_commands

def save_sql_to_file(commands, filename='db_init.sql'):
    with open(filename, 'w', encoding='utf-8') as file:
        file.write('\n'.join(commands))
    print(f"SQL скрипт сохранен в файл: {filename}")

def main():
    print("Генерация SQL скрипта...")
    
    sql_commands = generate_sql_script()
    
    save_sql_to_file(sql_commands, 'db_init.sql')

if __name__ == "__main__":
    main()