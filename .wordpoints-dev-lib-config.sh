#!/usr/bin/env bash

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# This needs to be set for caching to work.
	export WP_HTTP_TC_HOST=example.com

	# Exclude WP HTTP TestCase cache files from the disallowed strings check.
	CODESNIFF_PATH_STRINGS=("${CODESNIFF_PATH_STRINGS[@]}" '!' -path './tests/phpunit/cache/wp-http-tc/*')

	# Ignore lines that currently still need to have 'HTTP' in them.
	CODESNIFF_IGNORED_STRINGS=("${CODESNIFF_IGNORED_STRINGS[@]}" -e http://example.org -e 'this->url')
}

# EOF
