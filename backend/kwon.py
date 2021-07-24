from playwright.sync_api import sync_playwright
import pymysql
import time
import os
import random
import re
from datetime import date


class Kwon:
    def __init__(self):
        self.cat_stack = [None]
        data = self.get_database_data()
        self.connection = pymysql.connect(host=data["host"],
                                 user=data["user"],
                                 password=data["password"],
                                 database=data["database2"],
                                 cursorclass=pymysql.cursors.DictCursor)
        self.cursor = self.connection.cursor()

    def get_database_data(self):
        f = open(os.path.join(os.path.dirname(__file__), "mysql.txt"), "r", encoding="utf-8")
        lines = f.read().splitlines()
        data = dict((line.split("=")[0], line.split("=")[1]) for line in lines)
        return data

    def login(self):
        page = self.context.new_page()
        page.goto("https://www.kwon.com/account", wait_until="networkidle")
        if page.query_selector("a[class='cs-cookie__button cs-cookie__button--layer js-save-all-cookies']"):
            page.click("a[class='cs-cookie__button cs-cookie__button--layer js-save-all-cookies']", delay=150)
        if page.query_selector("#WHAT span.button--close"):
            page.click("#WHAT span.button--close", delay=150)
        page.type("#email", "thorsten.joos@gmail.com", delay=100)
        page.type("#passwort", "Superchecker", delay=100)
        page.click("button.register--login-btn", delay=150)
        time.sleep(5)
        page.close()

    def get_products(self):
        page = self.context.new_page()
        products = []
        i = 1
        while True:
            page.goto(f'https://www.kwon.com/produkte?p={i}', wait_until="networkidle")
            list = page.query_selector('div.listing')
            if list is None:
                break
            if len(list.query_selector_all('div.product--box')) == 0:
                break
            for product in list.query_selector_all('div.product--box'):
                if product.query_selector('.badge--oddment'):
                    products.append((product.query_selector('a').get_attribute('href'), True))
                else:
                    products.append(product.query_selector('a').get_attribute('href'))
            i += 1
            time.sleep(random.uniform(2, 3))
        page.close()
        return products

    def scrape_variants(self, products):
        page = self.context.new_page()
        not_scraped = []
        for url in products:
            restposten = False
            if type(url) is tuple:
                url = url[0]
                restposten = True
            try:
                page.goto(url, wait_until="networkidle")
            except:
                not_scraped.append(url)
                continue
            selects = page.query_selector_all("div.product--configurator select")
            if len(selects) == 0:
                self.scrape(page, None, None, restposten)
            elif len(selects) == 1:
                options = page.query_selector_all("div.product--configurator select")[0].query_selector_all("option:not([value='']):enabled")
                for option in options:
                    value = option.get_attribute('value')
                    selection1 = option.inner_html().strip()
                    page.query_selector_all("div.product--configurator select")[0].select_option(value)
                    time.sleep(random.uniform(2, 3))
                    try:
                        page.wait_for_selector("div.js--loading-indicator", state='hidden')
                        self.scrape(page, selection1, None, restposten)
                    except:
                        not_scraped.append(url)
                        continue
                    
            elif len(selects) == 2:
                options = page.query_selector_all("div.product--configurator select")[0].query_selector_all("option:not([value='']):enabled")
                print([option.inner_html().strip() for option in options])
                for option in options:
                    value = option.get_attribute('value')
                    selection1 = option.inner_html().strip()
                    print(value)
                    page.query_selector_all("div.product--configurator select")[0].select_option(value)
                    time.sleep(random.uniform(2, 3))
                    try:
                        page.wait_for_selector("div.js--loading-indicator", state='hidden')
                    except:
                        not_scraped.append(url)
                        continue

                    options = page.query_selector_all("div.product--configurator select")[1].query_selector_all("option:not([value='']):enabled")
                    print([option.inner_html().strip() for option in options])
                    for option in options:
                        value = option.get_attribute('value')
                        selection2 = option.inner_html().strip()
                        print(value)
                        page.query_selector_all("div.product--configurator select")[1].select_option(value)
                        time.sleep(random.uniform(2, 3))
                        try:
                            page.wait_for_selector("div.js--loading-indicator", state='hidden')
                            self.scrape(page, selection1, selection2, restposten)
                        except:
                            not_scraped.append(url)
                            continue

                    page.click("a.reset--configuration", delay=100)
                    time.sleep(random.uniform(2, 3))
                    try:
                        page.wait_for_selector("div.js--loading-indicator", state='hidden')
                    except:
                        not_scraped.append(url)
                        continue
            time.sleep(random.uniform(2, 3))
        page.close()
        print("Items not scraped because of error:")
        for url in not_scraped:
            print(url)

    def scrape(self, page, selection1, selection2, restposten):
        url = page.url
        print(url, selection1, selection2)
        title: str = page.query_selector("h1.product--title").inner_html().strip() if page.query_selector("h1.product--title") else None
        description: str = page.query_selector("div.product--description").inner_html().strip() if page.query_selector("div.product--description") else None
        sku: str = page.query_selector("li.entry--sku span").inner_text()
        if restposten and (sku.startswith('99') or sku.startswith('11')):
            sku = sku[2:]
        price = float(re.findall("\d+\.\d+", page.query_selector("span.price--content").inner_text().replace(",", "."))[0]) if page.query_selector("span.price--content") else None
        EK_price = float(re.findall("\d+\.\d+", page.query_selector("span.price--line-through").inner_text().replace(",", "."))[0]) if page.query_selector("span.price--line-through") else None
        UVP_price = float(re.findall("\d+\.\d+", page.query_selector("div.price--uvp").inner_text().split(": ")[1].replace(",", "."))[0]) if page.query_selector("div.price--uvp") else None
        images = [el.get_attribute("data-img-original") for el in page.query_selector_all("div.image-slider--container span.image--element")]
        categories = [el.inner_html() for el in page.query_selector_all('ul.breadcrumb--list li.breadcrumb--entry span.breadcrumb--title')]
        today = date.today()
        lastDate = today.strftime("%d/%m/%Y")
        print("d1 =", lastDate, type(lastDate))
        print(sku, title, price, EK_price, UVP_price, categories, images)
        self.cursor.execute("INSERT IGNORE INTO product (article_number, title, descrip, price, EK_price, UVP_price, selection1, selection2, categories, images, url, lastDate) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", (sku, title, description, price, EK_price, UVP_price, selection1, selection2, ",".join(categories), ",".join(images), url, lastDate))
        self.connection.commit()

    def run(self, playwright):
        self.browser = playwright.chromium.launch(headless=False)
        self.context = self.browser.new_context(
            user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
            viewport={ 'width': 1280, 'height': 1024 },
            locale='de-DE',
            timezone_id='Europe/Berlin',
        )
        self.login()
        products = self.get_products()
        self.scrape_variants(products)
        self.context.close()
        self.browser.close()

    def start(self):
        with sync_playwright() as playwright:
            self.run(playwright)

kwon = Kwon()
kwon.start()
