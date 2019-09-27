<?php

/**
 * @file
 * General settings.
 */

$settings = new stdClass();

/*
 * API Settings
 */
$settings->supermetricsApi = new stdClass();
$settings->supermetricsApi->url = 'https://api.supermetrics.com/assignment/';

/**
 * Assuming these values for this sample:
 */
$settings->assignment = new stdClass();
$settings->assignment->postsCount = 1000;
$settings->assignment->postsPerPage = 100;

/*
 * Client data (me).
 */
$settings->client = array();
$settings->client['client_id'] = 'ju16a6m81mhid5ue1z3v2g0uh';
$settings->client['email'] = 'mumazza@gmail.com';
$settings->client['name'] = 'Murilo Mazza';
