#!/usr/bin/env bash

# Sets up custom configuration.
function wordpoints-dev-lib-config() {

	# Use the develop branch for WPCS.
	export WPCS_GIT_TREE=develop

	# This needs to be set for caching to work.
	export WP_HTTP_TC_HOST=example.com
}

# EOF
