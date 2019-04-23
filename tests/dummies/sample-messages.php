<?php

return [
    /* "%Sample%", // %Desired result% */
    "'", // "'", not "&#039;"
    "…", // "…", not "&#8230;"
    "&#8230;", // "&#8230;", not "…"
    '<a href="#">...</a>' // -"-, not HTML link
];
