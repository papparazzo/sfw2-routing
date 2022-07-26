<?php

namespace SFW2\Routing\Enumerations;

enum RequestType {
    case AJAX_XML;
    case AJAX_JSON;
    case HTML;
}