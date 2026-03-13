@servers(['production' => 'user@serveur-ip'])

@setup
    $app_dir = "/var/www/jemenvole";
    $front_dir = "$app_dir/frontend";
    $back_dir = "$app_dir/backend";
    $branch = "main";
@endsetup

@story('deploy')
    clone_repository
    update_backend
    update_frontend
    restart_services
@endstory

### Étape 1 : Clonage ou mise à jour du code source
@task('clone_repository')
    echo "Clonage ou mise à jour du code source..."
    cd {{ $app_dir }}
    if [ ! -d ".git" ]; then
        git clone -b {{ $branch }} git@github.com:DevAlex79/BackJeMenvole.git .
    else
        git pull origin {{ $branch }}
    fi
@endtask

### Étape 2 : Déploiement du Backend Laravel
@task('update_backend')
    echo "Déploiement du Backend Laravel..."
    cd {{ $back_dir }}
    composer install --no-dev --optimize-autoloader
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --force
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan storage:link
@endtask

### Étape 3 : Déploiement du Frontend Vue.js
@task('update_frontend')
    echo "Déploiement du Frontend Vue.js..."
    cd {{ $front_dir }}
    npm install --production
    npm run build
    rm -rf /var/www/html/*
    cp -r dist/* /var/www/html/
@endtask

### Étape 4 : Redémarrage des services
@task('restart_services')
    echo "Redémarrage des services..."
    sudo systemctl restart php8.2-fpm
    sudo systemctl restart nginx
    echo "Déploiement terminé avec succès !"
@endtask
