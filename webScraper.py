from bs4 import BeautifulSoup
import requests
#url = "https://www.google.com"
url = "https://stackoverflow.com/questions/24398302/bs4-featurenotfound-couldnt-find-a-tree-builder-with-the-features-you-requeste"
# Make a GET request to fetch the raw HTML content
html_content = requests.get(url).text

# Parse the html content
#soup = BeautifulSoup(html_content, "html5lib")
#soup = BeautifulSoup(html_content, "xml")
soup = BeautifulSoup(html_content, "lxml")
print(soup.prettify()) # print the parsed data of html
