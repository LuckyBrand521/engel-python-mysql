import csv
import pymysql
import os

f = open(os.path.join(os.path.dirname(__file__), "mysql.txt"), "r", encoding="utf-8")
lines = f.read().splitlines()
data = dict((line.split("=")[0], line.split("=")[1]) for line in lines)

connection = pymysql.connect(host=data["host"],
                            user=data["user"],
                            password=data["password"],
                            database=data["database"],
                            cursorclass=pymysql.cursors.DictCursor)
cursor = connection.cursor()


with open('ean.csv') as csv_file:
    csv_reader = csv.reader(csv_file, delimiter=',')
    line_count = 0
    for row in csv_reader:
        if line_count != 0:
            sku = row[0]
            ean = row[5]
            uvp = float(row[7].replace(",","."))
            weight = float(row[9])
            quality = row[11]
            certification = row[12]
            print(row[0], row[5], row[7], row[9], row[11], row[12])
            cursor.execute('UPDATE size SET weight=%s, EAN=%s, UVP=%s, quality=%s, certification=%s WHERE sku=%s', (weight, ean, uvp, quality, certification, sku))
        line_count += 1
    connection.commit()
