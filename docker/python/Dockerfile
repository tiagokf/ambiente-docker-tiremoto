FROM python:3.11-slim

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    build-essential \
    libpq-dev \
    libmariadb-dev \
    pkg-config \
    nano \
    vim \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Atualizar pip
RUN pip install --upgrade pip

# Instalar ferramentas Python essenciais
RUN pip install \
    django \
    flask \
    fastapi \
    uvicorn \
    gunicorn \
    celery \
    redis \
    mysql-connector-python \
    PyMySQL \
    psycopg2-binary \
    requests \
    beautifulsoup4 \
    pandas \
    numpy \
    matplotlib \
    seaborn \
    jupyter \
    pytest \
    black \
    flake8 \
    pylint \
    python-dotenv

# Criar diretório de trabalho
WORKDIR /app

# Criar diretório para logs
RUN mkdir -p /var/log/python

# Expor portas comuns
EXPOSE 8000 5000 8888

# Comando padrão
CMD ["tail", "-f", "/dev/null"]
