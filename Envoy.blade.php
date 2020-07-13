@servers(['web' => 'josua@34.203.222.104'])@setup
    $repository = 'git@gitlab.com:pt-dot-internal/internship/laravel-pipeline.git';
    $releases_dir = '/var/www/releases';
    $app_dir = '/var/www/';
    $release = date('dmYHis');
    $new_release_dir = $releases_dir .'/'. $release;
@endsetup@story('deploy')
    clone_repository
    run_composer
    update_symlinks
@endstory@task('clone_repository')
    echo 'Cloning repository'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 {{ $repository }} {{ $new_release_dir }}
    echo 'Done'
@endtask@task('run_composer')
    echo "Starting deployment ({{ $release }})"
    cd {{ $new_release_dir }}
    composer install --prefer-dist --no-scripts -q -o
    echo 'Done'
@endtask@task('update_symlinks')
    echo "Linking storage directory"
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $app_dir }}/storage {{ $new_release_dir }}/storage
    echo 'Done'    echo 'Linking .env file'
    ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env
    echo 'Done'    echo 'Linking current release'
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
    echo 'Done'
@endtask