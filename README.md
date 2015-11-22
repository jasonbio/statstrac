# statstrac - open NFL statistics platform

statstrac visualizes, compares and manipulates raw NFL statistics from [nfldb](https://github.com/BurntSushi/nfldb) in the hopes of creating a flexible, easy to use web-based tool for research, projections and fun. http://statstrac.com/

By using [PostgREST](https://github.com/begriffs/postgrest) to expose RESTful access to [nfldb](https://github.com/BurntSushi/nfldb), we now have an extremely flexible dataset to have fun with. statstrac implements a local caching system as well for performance improvements and to avoid resource usage on redundant data that doesn't change. http://statstrac.com/ runs [PostgREST](https://github.com/begriffs/postgrest) locally for security reasons (however it should be safe enough to run publicly) and uses PHP to request & cache data.

**Requirements**

* [nfldb](https://github.com/BurntSushi/nfldb) - must have a running instance of nfdb via PostgreSQL
* [PostgREST](https://github.com/begriffs/postgrest) - Run PostgREST locally for easy RESTful access to nfldb
* Web server running PHP>=5.4

**Installation**

Coming soon

**Architecture**

Coming soon

**Caching**

Coming soon

**Disclaimer**

statstrac is not affiliated with The NFL. All logos, names, and other trademarks are copyright of their respective owners. statstrac makes no guarantee about the accuracy or completeness of the information herein.