## Using Google Calendar to Manage Library Website Hours

This is the code from Andrew Darby's article from the 
*[Code4Lib Journal](http://journal.code4lib.org/articles/46)*
published in 2008.

While the use of the Zend Gdata Client Library is still working fine, there are
changes to how the database table is configured and how the data is represented that 
I want to change.

1. I want to make the MySQL queries more secure by using prepared stateents and placeholders. 
2. The existing code does not have a way to handle instances when the library is open 24 hours. The may be a corresponding best practice for formating entries in the underlying Google Calendar. 
3. I want to use and API approach for generating the calendar on the website. 
