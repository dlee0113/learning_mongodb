#!/usr/bin/ruby

# example.rb

require 'rubygems'
require 'mongo'

include Mongo

host = ENV['MONGO_RUBY_DRIVER_HOST'] || 'localhost'
port = ENV['MONGO_RUBY_DRIVER_PORT'] || MongoClient::DEFAULT_PORT

# Connecting to #{host}:#{port}"
db = MongoClient.new(host, port).db('mydb')
coll = db.collection('test')

# Insert 1 record
coll.insert({'a' => Time.new.to_s })

# Find documents
puts "There are #{coll.count()} records in the test collection. Here they are:"
coll.find().each { |doc| puts doc.inspect }
