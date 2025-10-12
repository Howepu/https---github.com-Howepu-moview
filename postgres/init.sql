-- 1. Создаем таблицу режиссеров
CREATE TABLE directors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    bio TEXT,
    photo_url VARCHAR(255)
);

-- 2. Создаем таблицу жанров
CREATE TABLE genres (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon_url VARCHAR(255),
    description TEXT
);

-- 3. Создаем основную таблицу фильмов (с полем description)
CREATE TABLE movies (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    duration INT NOT NULL,
    country VARCHAR(100),
    director_id INTEGER REFERENCES directors(id),
    poster_url VARCHAR(255),
    description TEXT,
    rating FLOAT
);

-- 4. Создаем связующую таблицу для жанров фильмов (многие-ко-многим)
CREATE TABLE movie_genres (
    movie_id INTEGER REFERENCES movies(id),
    genre_id INTEGER REFERENCES genres(id),
    PRIMARY KEY (movie_id, genre_id)
);

-- 5. Создаем таблицу актёров
CREATE TABLE actors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    photo_url VARCHAR(255),
    bio TEXT
);

-- 6. Создаем связующую таблицу для актёров фильмов (многие-ко-многим)
CREATE TABLE movie_actors (
    movie_id INTEGER REFERENCES movies(id),
    actor_id INTEGER REFERENCES actors(id),
    PRIMARY KEY (movie_id, actor_id)
);

-- 7. Заполняем таблицу режиссеров
INSERT INTO directors (name, photo_url, bio) VALUES
('Гай Ричи', 'https://avatars.mds.yandex.net/get-entity_search/2321801/952259934/SUx182_2x', 'Британский режиссер, известный своими криминальными комедиями'),
('Кристофер Нолан', 'https://cybersport-img.cdnvideo.ru/images/og-jpg/plain/97/973d27e2-c7a3-4a29-a49b-49c1c8921d6a.webp', 'Американский режиссер, мастер интеллектуального кино'),
('Оливье Накаш и Эрик Толедано', 'https://avatars.mds.yandex.net/i?id=2475fd85992cf577d7a0d6698aeefaedb4dc7af3016ef1d7-6917316-images-thumbs&n=13', 'Французский режиссерский дуэт'),
('Фрэнк Дарабонт', 'https://avatars.mds.yandex.net/get-entity_search/42097/1132264037/SUx182_2x', 'Американский режиссер и сценарист'),
('Квентин Тарантино', 'https://avatars.mds.yandex.net/get-entity_search/4739159/791923121/SUx182', 'Американский режиссер, культовая фигура'),
('Роберт Земекис', 'https://avatars.mds.yandex.net/get-entity_search/5535095/785130599/S600xU_2x', 'Американский режиссер и продюсер'),
('Фрэнсис Форд Коппола', 'https://avatars.mds.yandex.net/get-entity_search/1579191/857228259/SUx182_2x', 'Легендарный американский режиссер'),
('Мартин Скорсезе', 'https://avatars.mds.yandex.net/get-entity_search/5098055/1131945373/SUx182_2x', 'Американский кинорежиссёр'),
('Дэвид Финчер', 'https://avatars.mds.yandex.net/get-kinopoisk-image/1773646/b7916266-133e-4c17-b95e-e0c219943fda/3840x', 'Американский режиссер, мастер триллеров'),
('Питер Джексон', 'https://avatars.mds.yandex.net/get-entity_search/2048976/954049122/SUx182_2x', 'Новозеландский режиссер, создатель Властелина Колец'),
('Ридли Скотт', 'https://avatars.mds.yandex.net/get-entity_search/1879189/1133266099/SUx182_2x', 'Британский режиссер, мастер эпического кино'),
('Джеймс Кэмерон', 'https://avatars.mds.yandex.net/get-entity_search/5505928/953154934/SUx182_2x', 'Канадский режиссер, создатель Титаника и Аватара'),
('Лана и Лилли Вачовски', 'https://avatars.mds.yandex.net/get-entity_search/1579191/857228259/SUx182_2x', 'Американские режиссеры, создатели Матрицы');

-- 8. Заполняем таблицу жанров
INSERT INTO genres (name, icon_url, description) VALUES
('криминал', 'https://i.pinimg.com/736x/0d/b3/14/0db3145dad7888f428ed4ce24a4c7836.jpg', 'Фильмы о преступном мире и мафии'),
('драма', 'https://avatars.mds.yandex.net/get-ott/1648503/2a00000170151228609cebf3f79fc0812bdb/256x384', 'Эмоциональные фильмы о человеческих отношениях'),
('фантастика', 'https://static.kinoafisha.info/upload/articles/599507571934.jpg', 'Научная фантастика и футуристические миры'),
('боевик', 'https://avatars.mds.yandex.net/get-ott/1652588/2a000001867933df86991a0a7ae0ed78ee98/256x384', 'Динамичные фильмы с экшеном'),
('фэнтези', 'https://avatars.mds.yandex.net/i?id=cbd5c0e8f866a09a7c6a1f70865ac8c2_l-5185705-images-thumbs&n=13', 'Волшебные миры и магия'),
('исторический', 'https://avatars.mds.yandex.net/get-ott/2419418/2a00000178db661f735e8c376668da7f5993/256x384', 'Фильмы о реальных исторических событиях');

-- 9. Заполняем таблицу актёров
INSERT INTO actors (name, photo_url, bio) VALUES
('Джейсон Флеминг', 'https://example.com/actors/flemyng.jpg', 'Британский актёр, известный по ролям в криминальных комедиях'),
('Декстер Флетчер', 'https://example.com/actors/fletcher.jpg', 'Британский актёр и режиссёр'),
('Ник Моран', 'https://example.com/actors/moran.jpg', 'Британский актёр и сценарист'),
('Джейсон Стэйтем', 'https://example.com/actors/statham.jpg', 'Британский актёр, звезда боевиков'),
('Стивен Макинтош', 'https://example.com/actors/mackintosh.jpg', 'Британский актёр театра и кино'),
('Винни Джонс', 'https://example.com/actors/jones.jpg', 'Бывший футболист, актёр'),
('Ленни Маклин', 'https://example.com/actors/maclean.jpg', 'Британский боксёр и актёр');

-- 10. Заполняем таблицу фильмов (с описанием для "Карты, деньги, два ствола")
INSERT INTO movies (title, year, duration, country, director_id, poster_url, description) VALUES
('Карты, деньги, два ствола', 1998, 107, 'Великобритания', 
 (SELECT id FROM directors WHERE name = 'Гай Ричи'),
 'https://img.ixbt.site/live/images/original/24/96/61/2024/04/03/564608fb8b.jpg',
 'Четверо молодых парней накопили каждый по 25 тысяч фунтов, чтобы один из них мог сыграть в карты с опытным шулером и матерым преступником, известным по кличке Гарри Топор. Парень в итоге проиграл 500 тысяч, на уплату долга ему дали неделю.'),

('Джентельмены', 2019, 113, 'Великобритания',
 (SELECT id FROM directors WHERE name = 'Гай Ричи'),
 'https://avatars.mds.yandex.net/i?id=f9b4eaa815cb05b92e4ca0313981d26f96f20e8d-13083617-images-thumbs&n=13', NULL),

('Большой куш', 2000, 104, 'Великобритания',
 (SELECT id FROM directors WHERE name = 'Гай Ричи'),
 'https://i.pinimg.com/736x/0d/b3/14/0db3145dad7888f428ed4ce24a4c7836.jpg', NULL),

('Intouchables', 2011, 112, 'Франция',
 (SELECT id FROM directors WHERE name = 'Оливье Накаш и Эрик Толедано'),
 'https://avatars.mds.yandex.net/i?id=4a025e8a5426ed931b6194e08cfff2c5b78458e8-5234044-images-thumbs&n=13', NULL),

('Interstellar', 2014, 169, 'США',
 (SELECT id FROM directors WHERE name = 'Кристофер Нолан'),
 'https://static.kinoafisha.info/upload/articles/599507571934.jpg', NULL),

('The Shawshank Redemption', 1994, 142, 'США',
 (SELECT id FROM directors WHERE name = 'Фрэнк Дарабонт'),
 'https://avatars.mds.yandex.net/get-ott/1648503/2a00000170151228609cebf3f79fc0812bdb/256x384', NULL),

('Inception', 2010, 148, 'США',
 (SELECT id FROM directors WHERE name = 'Кристофер Нолан'),
 'https://avatars.mds.yandex.net/i?id=10a416634a2e58f2c873a622f39ef0c5_l-6994641-images-thumbs&n=13', NULL),

('The Dark Knight', 2008, 152, 'США',
 (SELECT id FROM directors WHERE name = 'Кристофер Нолан'),
 'https://avatars.mds.yandex.net/get-ott/200035/2a000001673fe7e7c4e6a78ffd6079d74062/256x384', NULL),

('Pulp Fiction', 1994, 154, 'США',
 (SELECT id FROM directors WHERE name = 'Квентин Тарантино'),
 'https://avatars.mds.yandex.net/get-ott/2419418/2a0000017015126106578a9dc498a0625f06/256x384', NULL),

('Forrest Gump', 1994, 142, 'США',
 (SELECT id FROM directors WHERE name = 'Роберт Земекис'),
 'https://avatars.mds.yandex.net/get-ott/224348/2a0000016126cd92137d5218e4d0c7c8d798/256x3840', NULL),

('The Godfather', 1972, 175, 'США',
 (SELECT id FROM directors WHERE name = 'Фрэнсис Форд Коппола'),
 'https://avatars.mds.yandex.net/i?id=060528744b47d45c843231e925718d01_l-5222073-images-thumbs&n=13', NULL),

('The Matrix', 1999, 136, 'США',
 (SELECT id FROM directors WHERE name = 'Лана и Лилли Вачовски'),
 'https://avatars.mds.yandex.net/get-ott/1652588/2a000001867933df86991a0a7ae0ed78ee98/256x384', NULL),

('Fight Club', 1999, 139, 'США',
 (SELECT id FROM directors WHERE name = 'Дэвид Финчер'),
 'https://avatars.mds.yandex.net/get-ott/1534341/2a0000017924bbfa9fc3a82b229c4346a31c/256x384', NULL),

('The Lord of the Rings: The Fellowship of the Ring', 2001, 178, 'США',
 (SELECT id FROM directors WHERE name = 'Питер Джексон'),
 'https://avatars.mds.yandex.net/i?id=cbd5c0e8f866a09a7c6a1f70865ac8c2_l-5185705-images-thumbs&n=13', NULL),

('Gladiator', 2000, 155, 'США',
 (SELECT id FROM directors WHERE name = 'Ридли Скотт'),
 'https://avatars.mds.yandex.net/i?id=819c406b6465e0b35948cd5944ef9d89_l-5234092-images-thumbs&n=13', NULL),

('Titanic', 1997, 194, 'США',
 (SELECT id FROM directors WHERE name = 'Джеймс Кэмерон'),
 'https://avatars.mds.yandex.net/get-ott/2419418/2a00000178db661f735e8c376668da7f5993/256x384', NULL),

('Гнев человеческий', 2021, 118, 'США',
 (SELECT id FROM directors WHERE name = 'Гай Ричи'),
 'https://avatars.mds.yandex.net/i?id=f58be0cef8d34d06d7b31b25daeecd5dd16eceb9-3071260-images-thumbs&n=13', NULL),

('Агенты А.Н.К.Л.', 2015, 116, 'США',
 (SELECT id FROM directors WHERE name = 'Гай Ричи'),
 'https://avatars.mds.yandex.net/get-ott/1652588/2a00000167a6be08aef125bc7cf6fab6dc8b/256x384', NULL);

-- 11. Заполняем связь фильмов и жанров
INSERT INTO movie_genres (movie_id, genre_id) VALUES
-- Криминальные фильмы
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM genres WHERE name = 'криминал')),
((SELECT id FROM movies WHERE title = 'Джентельмены'), (SELECT id FROM genres WHERE name = 'криминал')),
((SELECT id FROM movies WHERE title = 'Большой куш'), (SELECT id FROM genres WHERE name = 'криминал')),
((SELECT id FROM movies WHERE title = 'Pulp Fiction'), (SELECT id FROM genres WHERE name = 'криминал')),
((SELECT id FROM movies WHERE title = 'The Godfather'), (SELECT id FROM genres WHERE name = 'криминал')),

-- Драмы
((SELECT id FROM movies WHERE title = 'Intouchables'), (SELECT id FROM genres WHERE name = 'драма')),
((SELECT id FROM movies WHERE title = 'The Shawshank Redemption'), (SELECT id FROM genres WHERE name = 'драма')),
((SELECT id FROM movies WHERE title = 'Forrest Gump'), (SELECT id FROM genres WHERE name = 'драма')),
((SELECT id FROM movies WHERE title = 'Fight Club'), (SELECT id FROM genres WHERE name = 'драма')),
((SELECT id FROM movies WHERE title = 'Titanic'), (SELECT id FROM genres WHERE name = 'драма')),

-- Фантастика
((SELECT id FROM movies WHERE title = 'Interstellar'), (SELECT id FROM genres WHERE name = 'фантастика')),
((SELECT id FROM movies WHERE title = 'Inception'), (SELECT id FROM genres WHERE name = 'фантастика')),
((SELECT id FROM movies WHERE title = 'The Matrix'), (SELECT id FROM genres WHERE name = 'фантастика')),

-- Боевики
((SELECT id FROM movies WHERE title = 'The Dark Knight'), (SELECT id FROM genres WHERE name = 'боевик')),
((SELECT id FROM movies WHERE title = 'Гнев человеческий'), (SELECT id FROM genres WHERE name = 'боевик')),
((SELECT id FROM movies WHERE title = 'Агенты А.Н.К.Л.'), (SELECT id FROM genres WHERE name = 'боевик')),

-- Фэнтези
((SELECT id FROM movies WHERE title = 'The Lord of the Rings: The Fellowship of the Ring'), (SELECT id FROM genres WHERE name = 'фэнтези')),

-- Исторические
((SELECT id FROM movies WHERE title = 'Gladiator'), (SELECT id FROM genres WHERE name = 'исторический'));

-- 12. Заполняем связь фильмов и актёров для "Карты, деньги, два ствола"
INSERT INTO movie_actors (movie_id, actor_id) VALUES
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Джейсон Флеминг')),
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Декстер Флетчер')),
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Ник Моран')),
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Джейсон Стэйтем')),
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Стивен Макинтош')),
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Винни Джонс')),
((SELECT id FROM movies WHERE title = 'Карты, деньги, два ствола'), (SELECT id FROM actors WHERE name = 'Ленни Маклин'));