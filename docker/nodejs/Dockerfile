# Используем стабильную версию Node
FROM node:20-alpine

WORKDIR /app

# 1. Принимаем аргумент из docker-compose (section 'args')
ARG VITE_API_URL

# 2. Устанавливаем его как системную переменную внутри образа
# Это критично, чтобы Vite увидел её при запуске dev-сервера
ENV VITE_API_URL=$VITE_API_URL

# Копируем файлы зависимостей
COPY frontend/package*.json ./

# Устанавливаем зависимости
# RUN npm install
RUN npm ci --prefer-offline --no-audit
# RUN npm install --prefer-offline --no-audit --no-fund

# Копируем исходный код
COPY frontend/ .

# Открываем порт Vite
EXPOSE 5173

# Запускаем Vite с прослушиванием всех интерфейсов (0.0.0.0)
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
