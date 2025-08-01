-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:8889
-- Время создания: Июл 31 2025 г., 10:37
-- Версия сервера: 8.0.35
-- Версия PHP: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `turkmenlove`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5'),
(2, 'kerim', '65807499');

-- --------------------------------------------------------

--
-- Структура таблицы `cake_details`
--

CREATE TABLE `cake_details` (
  `product_id` int NOT NULL,
  `weight` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `cake_details`
--

INSERT INTO `cake_details` (`product_id`, `weight`) VALUES
(5, '2000 gm'),
(6, '2500 gm'),
(7, '1500 gm'),
(8, '3000 gm');

-- --------------------------------------------------------

--
-- Структура таблицы `flower_details`
--

CREATE TABLE `flower_details` (
  `product_id` int NOT NULL,
  `flower_count` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `flower_details`
--

INSERT INTO `flower_details` (`product_id`, `flower_count`) VALUES
(1, 33),
(2, 21),
(3, 55),
(4, 121);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_phone` varchar(30) DEFAULT NULL,
  `reciever_name` varchar(100) DEFAULT NULL,
  `reciever_phone` varchar(30) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `no_address` tinyint(1) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `apartment` int DEFAULT NULL,
  `gate` int DEFAULT NULL,
  `floor` int DEFAULT NULL,
  `comment` text,
  `delivery_date` varchar(50) DEFAULT NULL,
  `delivery_time` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `quantity` int NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `client_name`, `client_email`, `client_phone`, `reciever_name`, `reciever_phone`, `city`, `no_address`, `address`, `apartment`, `gate`, `floor`, `comment`, `delivery_date`, `delivery_time`, `created_at`, `quantity`, `product_id`) VALUES
(1, 'Ahmet', 'mehmetkerimnazarov@gmail.com', '1234567890', 'Selbi', '132456789', 'Туркменабат', 0, 'Россия, Московская обл, г Домодедово', 45, 2, 7, 'sdelayte vse chetko', '25 Июль 2025', '20:00-22:00', '2025-07-15 13:26:19', 0, 0),
(2, 'Merdan', 'merdan@gmail.com', '1234556777', 'Svetlana', '12345556543', 'Туркменабат', 0, 'Россия, Московская обл, г Домодедово', 15, 2, 3, 'Podqarite s lyubovyu', '30 Июль 2025', '18:00-20:00', '2025-07-15 16:43:27', 0, 0),
(3, 'Merdan', 'merdan@gmail.com', '21323423423', 'Svetlana', '2143241343121', 'Туркменабат', 0, 'Россия, Московская обл, г Домодедово', 12, 31, 32, 'egergergergergergre', '30 Июль 2025', '18:00-20:00', '2025-07-15 16:44:51', 0, 0),
(4, 'Arslan', 'arsaln@gmail.com', '123456764', 'Malika', '12345567', 'Ашхабад', 0, 'Россия, Московская обл, г Домодедово', 12, 32, 32, '3efrwfwefewfdewf', '31 Июль 2025', '11:00-13:00', '2025-07-16 15:17:14', 0, 0),
(5, 'Keymir', 'keymir@gmail.com', '1231343553', 'jepbar', '12123413413', 'Туркменабат', NULL, 'Россия, Московская обл, г Домодедово', 12, 3, 4, 'podari s lyubovyu', '26 Июль 2025', '18:00&ndash;20:00', '2025-07-17 08:20:31', 0, 0),
(6, 'Ahmet', 'merdan@gmail.com', '123435235', 'Svetlana', '21324323', 'Туркменабат', NULL, 'Россия, Московская обл, г Домодедово', 123, 21, 43, '121212', '30 Июль 2025', '10:00&ndash;12:00', '2025-07-28 06:30:06', 1, 2),
(7, 'Ahmet', 'mehmetkerimnazarov@gmail.com', '1321442123', 'Selbi', '132124235', 'Туркменабат', NULL, 'Россия, Московская обл, г Домодедово', 1, 2, 0, '', '30 Июль 2025', '10:00&ndash;12:00', '2025-07-28 10:07:54', 1, 2),
(8, 'Gulnaz', 'gulnaz@gmail.com', '1234567890', 'Bayram', '12345678', 'Туркменабат', NULL, 'Parahat 7/5', 45, 2, 7, 'Tizrak bolun', '01 Август 2025', '18:00&ndash;20:00', '2025-07-31 10:34:08', 1, 2),
(9, 'Gulnaz', 'gulnaz@gmail.com', '1234567890', 'Bayram', '12345678', 'Туркменабат', NULL, 'Parahat 7/5', 45, 2, 7, 'Tizrak bolun', '01 Август 2025', '18:00&ndash;20:00', '2025-07-31 10:34:08', 1, 10),
(10, 'Gulnaz', 'gulnaz@gmail.com', '1234567890', 'Bayram', '12345678', 'Туркменабат', NULL, 'Parahat 7/5', 45, 2, 7, 'Tizrak bolun', '01 Август 2025', '18:00&ndash;20:00', '2025-07-31 10:34:08', 3, 12),
(11, 'Gulnaz', 'gulnaz@gmail.com', '1234567890', 'Bayram', '12345678', 'Туркменабат', NULL, 'Parahat 7/5', 45, 2, 7, 'Tizrak bolun', '01 Август 2025', '18:00&ndash;20:00', '2025-07-31 10:34:08', 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `price` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category`) VALUES
(1, 'Алые Грезы', 'Букет из насыщенно-красных роз — символ страсти, любви и уверенности. Идеален для признаний и важных моментов.', '500 ', 'flower1.jpg', 'flower'),
(2, 'Солнечный Ветер', 'Композиция из жёлтых тюльпанов и ромашек. Поднимает настроение и согревает теплом даже в пасмурный день.', '300 ', 'flower2.webp', 'flower'),
(3, 'Весеннее Утро', 'Нежный букет из пионов, гиацинтов и сирени. Дарит ощущение свежести и лёгкости первых весенних дней.', '400 ', 'flower3.jpeg', 'flower'),
(4, 'Нежность Востока', 'Орхидеи, лилии и белые розы в минималистичном оформлении. Элегантность с восточной ноткой.', '600', 'flower4.webp', 'flower'),
(5, 'Лавандовый Рай', 'Композиция из лаванды, роз и эвкалипта. Наполняет воздух ароматом спокойствия и прованского уюта.', '800', 'cake1.jpeg', 'cake'),
(6, 'Тропический Вздох', 'Экзотика антуриума, статицы и альстромерий. Для тех, кто любит нестандартные решения и яркие эмоции.', '900', 'cake2.webp', 'cake'),
(7, 'Золотое Настроение', 'Оранжевые герберы, розы и подсолнухи — букет, как солнечный привет. Дарит тепло и позитив.', '650', 'cake3.webp', 'cake'),
(8, 'Бархат Любви', 'Классический букет из бордовых и пудровых роз, оформленных в бархатную упаковку. Для самых чувственных моментов.', '750', 'cake4.jpeg', 'cake'),
(9, 'Amazon Gift', '', '550', 'gift1.jpg', 'giftcard'),
(10, 'Visa Gift', '', '340', 'gift2.jpg', 'giftcard'),
(11, 'Apple Gift', '', '125', 'gift3.webp', 'giftcard'),
(12, 'Xbox Gift', '', '345', 'gift4.webp', 'giftcard'),
(13, 'Медвежонок Лаки', 'мягкий, тёплый, с бантиком', '20', 'toy1.jpg', 'toy'),
(14, 'Зайка Карамелька', 'пушистая, милая, с ушками', '10', 'toy2.jpeg', 'toy'),
(15, 'Котик Снежок', 'белый, нежный, с глазками', '15', 'toy3.jpg', 'toy'),
(16, 'Панда Чуи', 'большая, мягкая, обнимательная', '12', 'toy4.jpeg', 'toy');

-- --------------------------------------------------------

--
-- Структура таблицы `toy_details`
--

CREATE TABLE `toy_details` (
  `product_id` int NOT NULL,
  `size` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `toy_details`
--

INSERT INTO `toy_details` (`product_id`, `size`) VALUES
(13, '33x33'),
(14, '55x55'),
(15, '11x11'),
(16, '44x44');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Индексы таблицы `cake_details`
--
ALTER TABLE `cake_details`
  ADD PRIMARY KEY (`product_id`);

--
-- Индексы таблицы `flower_details`
--
ALTER TABLE `flower_details`
  ADD PRIMARY KEY (`product_id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `toy_details`
--
ALTER TABLE `toy_details`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cake_details`
--
ALTER TABLE `cake_details`
  ADD CONSTRAINT `cake_details_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `flower_details`
--
ALTER TABLE `flower_details`
  ADD CONSTRAINT `flower_details_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ограничения внешнего ключа таблицы `toy_details`
--
ALTER TABLE `toy_details`
  ADD CONSTRAINT `toy_details_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
