# statstrac - open NFL statistics platform

statstrac visualizes, compares and manipulates raw NFL statistics from [nfldb](https://github.com/BurntSushi/nfldb) in the hopes of creating a flexible, easy to use web-based tool for research, projections and fun. http://statstrac.com/

**_statstrac is still very much in beta and not all views/pages/stats are working yet._**

**Concept**

Using [PostgREST](https://github.com/begriffs/postgrest) to expose RESTful access to [nfldb](https://github.com/BurntSushi/nfldb) yields an extremely flexible dataset to have fun with. statstrac requests data from [nfldb](https://github.com/BurntSushi/nfldb) through [PostgREST](https://github.com/begriffs/postgrest) and then visualizes the results using Bootstrap, jQuery, and other front-end tools. statstrac also caches data for performance improvements by identifying static routes that are not likely to change for long periods of time (past seasons, inactive players, etc). statstrac runs [PostgREST](https://github.com/begriffs/postgrest) locally, like an internal API.

**Requirements**

* [nfldb](https://github.com/BurntSushi/nfldb) - must have a running instance of nfdb via PostgreSQL
* [PostgREST](https://github.com/begriffs/postgrest) - Run PostgREST locally for easy RESTful access to nfldb
* Web server running PHP>=5.4

**Installation**

Coming soon

**Disclaimer**

statstrac is not affiliated with The NFL. All logos, names, and other trademarks are copyright of their respective owners. statstrac makes no guarantee about the accuracy or completeness of the information herein.
