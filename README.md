WordPointsOrg [![Build Status](https://travis-ci.org/WordPoints/wordpointsorg.svg?branch=develop)](https://travis-ci.org/WordPoints/wordpointsorg) [![Coverage Status](https://coveralls.io/repos/WordPoints/wordpointsorg/badge.svg?branch=develop)](https://coveralls.io/r/WordPoints/wordpointsorg?branch=develop) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WordPoints/wordpointsorg/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/WordPoints/wordpointsorg/?branch=develop) [![HackerOne Bug Bounty Program](https://img.shields.io/badge/security-HackerOne-blue.svg)](https://hackerone.com/wordpoints)
=============

WordPoints module for installing modules from WordPoints.org

## About

This module started as an effort to create an installation and update experience for modules on WordPoints.org similar to that for plugins from WordPress.org. As work on it has progressed, the decision was made to produce something that would be more versatile than the plugin API of WordPress. Thus, this module has been designed in such a way that its use is by no means limited to WordPoints.org. It provides an extensible interface between the module consumer's site and the remote module repo. Its goal is to provide a consistent experience for the user (and developer) on the consumer end, while still giving the repo the freedom to design their API however they please.

Within this module's jargon, the remote repo is called a _Channel_ (think TV), and the module communicates with it using one of potentially many available _APIs_. This module's job is to provide the code interface which handles relaying this communication to the user. The actual communication with the Channel is handled through an API handler installed on the consumer's site.

For example, if a Channel provides its modules using the EDD Software Licenses extension of the Easy Digital Downloads plugin, the consumer site will need to have installed an API handler for the API provided by that plugin. A handler for this particular API is included with this module, which will allow you to update modules from WordPoints.org (since it uses the EDD Software Licenses plugin).

Currently, the interface this module provides only covers module updates. Installing modules and browsing the remote repo from your admin panels are features that are planned for future releases.

Once all of the features has been implemented and this module is mature, it will probably end up being merged into the WordPoints plugin!
