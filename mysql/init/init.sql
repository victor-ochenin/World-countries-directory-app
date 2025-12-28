CREATE TABLE country_t (
    id INT NOT NULL AUTO_INCREMENT,
    short_name_f NVARCHAR(100) NOT NULL,
    full_name_f NVARCHAR(200) NOT NULL,
    iso_alpha2_f CHAR(2) NOT NULL,
    iso_alpha3_f CHAR(3) NOT NULL,
    iso_numeric_f CHAR(3) NOT NULL,
    population_f INT NOT NULL,
    --
    PRIMARY KEY(id),
    UNIQUE(iso_alpha2_f),
    UNIQUE(iso_alpha3_f),
    UNIQUE(iso_numeric_f)
);

DELETE FROM country_t;

ALTER TABLE country_t AUTO_INCREMENT = 1;

INSERT INTO country_t (
    short_name_f, 
    full_name_f, 
    iso_alpha2_f, 
    iso_alpha3_f, 
    iso_numeric_f, 
    population_f
)  VALUES 
    -- Россия
    (N'Россия', N'Российская Федерация', 'RU', 'RUS', '643', 146150789),
    
    -- США
    (N'США', N'Соединённые Штаты Америки', 'US', 'USA', '840', 334914895),
    
    -- Китай
    (N'Китай', N'Китайская Народная Республика', 'CN', 'CHN', '156', 1412000000),
    
    -- Германия
    (N'Германия', N'Федеративная Республика Германия', 'DE', 'DEU', '276', 83200000),
    
    -- Франция
    (N'Франция', N'Французская Республика', 'FR', 'FRA', '250', 67800000),
    
    -- Япония
    (N'Япония', N'Япония', 'JP', 'JPN', '392', 125000000),
    
    -- Великобритания
    (N'Великобритания', N'Соединённое Королевство Великобритании и Северной Ирландии', 'GB', 'GBR', '826', 67700000),
    
    -- Индия
    (N'Индия', N'Республика Индия', 'IN', 'IND', '356', 1380000000),
    
    -- Бразилия
    (N'Бразилия', N'Федеративная Республика Бразилия', 'BR', 'BRA', '076', 213000000),
    
    -- Австралия
    (N'Австралия', N'Австралийский Союз', 'AU', 'AUS', '036', 25700000);