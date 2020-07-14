@servers(['web' => 'josua@34.203.222.104'])

@setup
    $repository = 'git@gitlab.com:pt-dot-internal/internship/laravel-pipeline.git';
    $releases_dir = '/var/www/releases';
    $app_dir = '/var/www/';
    //$release = date('dmYHis');
    $release = getenv('CI_COMMIT_SHORT_SHA');
    $new_release_dir = $releases_dir .'/'. $release;
@endsetup

@story('deploy')
    clone_repository
    run_composer
    update_symlinks
    remove_directory
@endstory

@task('clone_repository')
    echo 'Cloning repository'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 {{ $repository }} {{ $new_release_dir }}
    echo 'Done'
@endtask

@task('run_composer')
    echo "Starting deployment ({{ $release }})"
    cd {{ $new_release_dir }}
    composer install --prefer-dist --no-scripts -q -o --ignore-platform-reqs
    echo 'Done'
@endtask

@task('update_symlinks')
    echo "Linking storage directory"
    rm -rf {{ $new_release_dir }}/storage
    ln -nfs {{ $app_dir }}/storage {{ $new_release_dir }}/storage
    echo 'Done'

    echo 'Linking .env file'
    ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env
    echo 'Done'

    echo 'Linking current release'
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
    echo 'Done'
@endtask

@task('remove_directory')
   echo "Checking if release directory more than 5..."
   cd /var/www/releases/
   dir_count=$(ls -t | wc -l)
   if (($dir_count > 5)); then rm -rf $(ls -t | tail -n1); echo "Removing directory"; else echo "Release directory is less than 5, do nothing"; fi
@endtask
