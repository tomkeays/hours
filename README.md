## Using Google Calendar to Manage Library Website Hours

This is the code from Andrew Darby's article from the 
*[Code4Lib Journal](http://journal.code4lib.org/articles/46)*
published in 2008.

While the use of the Zend Gdata Client Library is still working fine, there are
changes to how the database table is configured and how the data is represented that 
I want to change.


### Project Goals

1. Refactor database code to use PDO approach.
2. Make the database queries more secure by using prepared stateents and placeholders. 
3. Create a way to handle instances when the library is open 24 hours. The may be a corresponding best practice for formating entries in the underlying Google Calendar. 
4. Use and API approach for generating the calendar on the website. 
