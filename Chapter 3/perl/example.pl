#!/usr/bin/perl
#
#  example.pl
#

use MongoDB;
use DateTime;
 
# set up MongoDB connection to mydb / test
my $client     = MongoDB::MongoClient->new(host => 'localhost', port => 27017);
my $database   = $client->get_database( 'mydb' );
my $collection = $database->get_collection( 'test' );

# insert date time
my $dt         = DateTime->now;
my $id         = $collection->insert({ a => 'perl: ' .  $dt->datetime() });

# iterate through results
my $cursor     = $collection->find();
while (my $item = $cursor->next) {
        print $item->{'a'}."\n";
}
