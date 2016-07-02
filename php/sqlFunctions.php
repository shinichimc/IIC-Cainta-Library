<?php

  function sqlSearchByTitle ($s, $o, $i) {
    return "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where title like '%".$s."%' group by ISBN limit ".$o.",".$i;
  }

  function sqlSearchByAuthor ($s, $o, $i) {
    return "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where title like '%".$s."%' group by ISBN limit ".$o.",".$i;
  }

  function sqlSearchByYear ($s, $o, $i) {
    return "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having Authors like '%".$s."%' limit ".$o.",".$i;
  }
