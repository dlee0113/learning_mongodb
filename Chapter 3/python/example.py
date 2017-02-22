#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
#  example.py
#  

# example python script
import datetime
import pprint
import pymongo
from pymongo import MongoClient

# connect to MongoDB database "mydb"
client = MongoClient('mongodb://localhost:27017/')
db = client.mydb

# create a document and insert
post = { "a" : datetime.datetime.utcnow() }
postId = db.test.insert(post);

# find all documents in "test" collection
find = db.test.find()
for item in db.test.find():
   pprint.pprint(item)
