The code behind evebloggers.com, the fuzzysteve version.

It depends on postgres, rather then mysql, because mysql lacks window functions. I guess you could run it in oracle with no problems. 


Templating is handled by smarty, which you need to install as per http://www.smarty.net/quick_install

Parsing of RSS feeds is handled by http://simplepie.org/  http://simplepie.org/wiki/setup/setup The directory to upload to is utils/

The search engine is a copy of solr. I'm using the default configuration. php accesses it through http://www.solarium-project.org/, for which you'll find a composer.json in the utils/indexer directory.



