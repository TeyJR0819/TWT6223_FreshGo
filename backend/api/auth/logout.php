<?php
require_once '../../helpers/response.php';

start_session();
session_destroy();
json_response(['message' => 'Logged out successfully']);
