from playwright.sync_api import sync_playwright
import pymysql
import time
import os
import random


class Engel:
    def __init__(self):
        self.cat_stack = [None]
        data = self.get_database_data()
        self.connection = pymysql.connect(host=data["host"],
                                 user=data["user"],
                                 password=data["password"],
                                 database=data["database"],
                                 cursorclass=pymysql.cursors.DictCursor)
        self.cursor = self.connection.cursor()

    def get_database_data(self):
        f = open(os.path.join(os.path.dirname(__file__), "mysql.txt"), "r", encoding="utf-8")
        lines = f.read().splitlines()
        data = dict((line.split("=")[0], line.split("=")[1]) for line in lines)
        return data

    def run(self, playwright):
        self.browser = playwright.chromium.launch(headless=True)
        self.context = self.browser.new_context(
            user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
            viewport={ 'width': 1280, 'height': 1024 },
            locale='de-DE',
            timezone_id='Europe/Berlin',
        )
        self.page = self.context.new_page()
        self.page.goto("https://b2b.engel-natur.de/", wait_until="networkidle")

        self.page.click("text=/.*Konfigurieren.*/")
        self.page.click("//body[starts-with(normalize-space(.), '.engel-global-banner { background-color: #e52147; background-image: -ms-linear-g')]")
        self.page.click("input[type=\"button\"]")
        self.page.click("input[name=\"email\"]")
        self.page.fill("input[name=\"email\"]", "waldwichtelshop@gmail.com")
        self.page.click("input[name=\"password\"]")
        self.page.fill("input[name=\"password\"]", "Angeldust2019*")
        self.page.click("text=/.*Anmelden.*/")
        time.sleep(1)

        self.parse_cat("https://b2b.engel-natur.de/engel/", 0)

        # Close
        self.page.close()
        self.context.close()
        self.browser.close()

    def parse_cat(self, url, level):
        page = self.context.new_page()
        page.goto(url, wait_until="networkidle")
        if page.query_selector(f'div.sidebar--categories-navigation ul.is--level{level}'):
            for cat in page.query_selector_all(f'div.sidebar--categories-navigation ul.is--level{level} > li > a'):
                cat_id = cat.get_attribute('data-categoryid')
                cat_name = cat.inner_text().strip()
                if cat_name == 'Vororder':
                    continue
                self.cursor.execute('INSERT IGNORE INTO category (cat_id, name, parent_id) VALUES (%s, %s, %s)', (cat_id, cat_name, self.cat_stack[-1]))
                self.connection.commit()
                self.cat_stack.append(cat_id)
                self.parse_cat(cat.get_attribute('href'), level + 1)
                self.cat_stack.pop()
        else:
            print(self.cat_stack)
            for product in page.query_selector_all('div.listing > div'):
                url = product.query_selector("a").get_attribute("href")
                self.parse_product(url)
        time.sleep(1)
        page.close()
                

    def parse_product(self, url):
        print(url)
        page = self.context.new_page()
        page.goto(url, wait_until="networkidle")
        title = page.query_selector('h1.product--title').inner_html().strip()
        description = page.query_selector('div.product--description').inner_html().strip()
        article_num = page.query_selector('li.entry-attribute > span.entry--content').inner_text().strip()
        if article_num[0] == "S" or article_num[0] == "s":
            article_num = article_num[1:]
            print(article_num, title)
        self.cursor.execute('INSERT IGNORE INTO product (article_num, cat, title, descrip, url) VALUES (%s, %s, %s, %s, %s) AS new ON DUPLICATE KEY UPDATE url=new.url', (article_num, self.cat_stack[-1], title, description, url))
        self.connection.commit()
        images = [(img.get_attribute('data-img-original'), img.get_attribute('data-alt')) for img in page.query_selector_all('div[class="image-slider--container no--thumbnails"] span.image--element')]
        self.cursor.executemany(f"INSERT IGNORE INTO images (article_num, img_url, alt) VALUES ('{article_num}', %s, %s)", images)
        self.connection.commit()
        table = page.query_selector('table[class="layout stutt--variant-matrix-two-groups"]')
        sizes = [size.inner_html().strip() for size in table.query_selector_all('thead > tr > th') if size.inner_html().strip() != '']
        for variant in table.query_selector_all('tbody > tr'):
            var = variant.query_selector('td > strong').inner_html()
            for i, size in enumerate(variant.query_selector_all('td')[1:]):
                price = ""
                status = "unknown"
                if size.query_selector('p.stutt--variant-price'):
                    price = size.query_selector('p.stutt--variant-price').inner_html()
                if size.query_selector('i'):
                    status = size.query_selector('i').get_attribute('class').strip().split(" ")[-1]
                #print(var, sizes[i], price, status)
                self.cursor.execute('INSERT INTO size (variant, size, price, product_id, stock) VALUES (%s, %s, %s, %s, %s) AS new ON DUPLICATE KEY UPDATE price=new.price, stock=new.stock', (var, sizes[i], price, article_num, status))
                self.connection.commit()
        time.sleep(random.uniform(2.5, 4.0))
        page.close()

    def start(self):
        with sync_playwright() as playwright:
            self.run(playwright)

engel = Engel()
engel.start()