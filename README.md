# Shellless
A PHP package to extract readable text from HTML.

# Installation

Execute the next command.

    composer require sukohi/shellless:1.*

# Usage


    use Sukohi\Shellless\Shellless;

    $html = file_get_contents('http://example.com/');
    $shellless = new Shellless();
    $result = $shellless->extract($html);

    echo $result->title;        // Page title
    
    echo $result->best_text;    // The longest text

    echo $result->full_text;    // Joined text if more than 100 characters length.
    
    print_r($result->all_texts, true);

# Options

    $shellless->setOptions([
        'join_step' => 5,
        'min_text_length' => 100
    ]);

# Algorithm

1. Join close texts if less than 5 HTML tags between them.
2. Pick up texts if more than 100 characters length.

# License

This package is licensed under the MIT License.  
Copyright 2017 Sukohi Kuhoh