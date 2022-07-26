<?php

namespace SFW2\Routing\Enumerations;

enum MethodType: string {
    case GET    = 'GET';
    case POST   = 'POST';
    case PUT    = 'PUT';
    case DELETE = 'DELETE';
}