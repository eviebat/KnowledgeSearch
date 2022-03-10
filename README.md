# KnowledgeSearch
This tool is an intranet app that allows the IT organization to search through their knowledge article database. The main logic of the app is in the kasearchmain.php file. 

![Capture3](https://user-images.githubusercontent.com/14208362/157765068-ce975c3a-3e95-4665-a659-6252c63744b2.PNG)

## Profiles
The knowledge article database consists of different profiles for different teams. A SQL server database holds each user and their preference for which knowledge articles to search through. This can be changed by toggling the "Profile Settings" button.
The user settings logic is defined in userSettings.php

![Capture2](https://user-images.githubusercontent.com/14208362/157765274-04bc4651-9faf-406b-ba1f-0723a6388f90.PNG)

## Searching
Searching can be done by Title, Service, Config Item or the entire article. The user is also able to check different regional prefixes to search for a knowledge articles that relate to a specific region. By selecting these different options, a different SQL query is created to match the desired search.

![Capture](https://user-images.githubusercontent.com/14208362/157765398-894463b2-553e-47e4-ba7f-506521997a66.PNG)
