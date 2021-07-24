import pymysql
import os

def get_database_data():
    f = open(os.path.join(os.path.dirname(__file__), "mysql.txt"), "r", encoding="utf-8")
    lines = f.read().splitlines()
    data = dict((line.split("=")[0], line.split("=")[1]) for line in lines)
    return data


data = get_database_data()
connection = pymysql.connect(host=data["host"],
                            user=data["user"],
                            password=data["password"],
                            database=data["database"],
                            cursorclass=pymysql.cursors.DictCursor)
cursor = connection.cursor()

cursor.execute("""
create table if not exists category
(
    cat_id    int         not null
        primary key,
    name      varchar(50) null,
    parent_id int         null,
    constraint category_category_cat_id_fk
        foreign key (parent_id) references category (cat_id)
);

create table if not exists product
(
    article_num varchar(30)  not null,
    cat         int          null,
    title       varchar(200) null,
    descrip     text         null,
    url         varchar(200) null,
    constraint product_article_num_uindex
        unique (article_num),
    constraint product_category_cat_id_fk
        foreign key (cat) references category (cat_id)
);

alter table product
    add primary key (article_num);

create table if not exists images
(
    article_num varchar(50)  not null,
    img_url     varchar(500) not null,
    alt         varchar(100) null,
    primary key (article_num, img_url),
    constraint images_product_article_num_fk
        foreign key (article_num) references product (article_num)
);

create index images_article_num_index
    on images (article_num);

create table if not exists size
(
    variant       varchar(50)                                                                                                         not null,
    size          varchar(20)                                                                                                         not null,
    price         varchar(20)                                                                                                         not null,
    product_id    varchar(30)                                                                                                         not null,
    stock         enum ('delivery--status-not-available', 'delivery--status-available', 'delivery--status-more-is-coming', 'unknown') null,
    weight        float                                                                                                               null,
    EAN           char(13)                                                                                                            null,
    UVP           float                                                                                                               null,
    quality       text                                                                                                                null,
    certification varchar(20)                                                                                                         null,
    sku           varchar(50)                                                                                                         null,
    primary key (product_id, variant, size),
    constraint size_product_article_num_fk
        foreign key (product_id) references product (article_num)
);

create index size_product_id_index
    on size (product_id);


""")
connection.commit()

connection = pymysql.connect(host=data["host"],
                            user=data["user"],
                            password=data["password"],
                            database=data["database2"],
                            cursorclass=pymysql.cursors.DictCursor)
cursor = connection.cursor()
cursor.execute("""
create table if not exists product
(
    article_number int          not null
        primary key,
    title          varchar(100) null,
    descrip        text         null,
    price          float        null,
    EK_price       float        null,
    UVP_price      float        null,
    selection1     varchar(50)  null,
    selection2     varchar(50)  null,
    categories     text         null,
    images         text         null,
    url            varchar(200) null
);
""")
connection.commit()