/* Example.java */

import com.mongodb.BasicDBObject;
import com.mongodb.DB;
import com.mongodb.DBCollection;
import com.mongodb.DBCursor;
import com.mongodb.MongoClient;

import java.util.List;
import java.util.Set;

public class Example {

    public static void main(String[] args) throws Exception {

        // connect to the local database server
        // NOTE: older versions of the *.jar file do not have a MongoClient class
        //       check your *.jar file to be sure
        //       use the Mongo class instead
        MongoClient mongoClient = new MongoClient();

        // get handle to "mydb"
        DB db = mongoClient.getDB("mydb");

        // get a collection object to work with
        DBCollection testCollection = db.getCollection("test");

        // make a document and insert it
        BasicDBObject doc = new BasicDBObject("a", "Java");

        testCollection.insert(doc);

        //  lets get all the documents in the collection and print them out
        DBCursor cursor = testCollection.find();
        try {
            while (cursor.hasNext()) {
                System.out.println(cursor.next());
            }
        } finally {
            cursor.close();
        }

        // See if the last operation had an error
        System.out.println("Last error : " + db.getLastError());

        // see if any previous operation had an error
        System.out.println("Previous error : " + db.getPreviousError());

        // release resources
        mongoClient.close();
    }
}
