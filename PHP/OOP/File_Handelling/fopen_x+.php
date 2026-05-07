<?php

    $file = fopen("unique_data.txt", "w");
    if ($file) {
        fwrite($file, "নতুন ডাটা লিখলাম।");
        rewind($file); // কার্সার শুরুতে নিয়ে আসা পড়ার জন্য
        echo fread($file, filesize("unique_data.txt"));
        fclose($file);
    } else {
        echo "ফাইলটি আগে থেকেই আছে, তাই ওপেন হয়নি!";
    }

?>