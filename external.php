<?php

$target   = realpath(getcwd().'/'.$argv[1]);
$reposDir = __DIR__.'/repositories/';
$external = json_decode(file_get_contents(__DIR__."/external.json"));

$updatedRepos = array();
foreach($external as $path => $data) {
    $repoDir = $reposDir.$data->repository;
    $repoUrl = 'https://github.com/phpixie/'.$data->repository;
    
    if(!in_array($data->repository, $updatedRepos)) {
        if(!is_dir($repoDir)) {
            passthru("git clone \"$repoUrl\" \"$repoDir\"");
        }else{
            passthru("git --git-dir=\"$repoDir/.git\" --work-tree=\"$repoDir\" pull");
        }
        
        $updatedRepos[]= $data->repository;
    }
    
    $markdown = file_get_contents("$repoDir/{$data->path}");
    preg_replace('#^```php.*#', '```php?start_inline', $markdown);
    
    $contents = "---\n";
    $contents.= "layout: page";
    $contents.= "---\n\n";
    $contents.= $markdown;
    
    file_put_contents($target.'/'.$path.'.html.markdown', $contents);
}