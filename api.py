import requests
import json
import os


status_names = {
    1: "STATUS_QUEUED",
    3: "STATUS_PUBLISHED",
    4: "STATUS_DECLINED",
    5: "STATUS_SCHEDULED",
}


def read_env_file(file_path):
    with open(file_path, "r") as file:
        for line in file:
            line = line.strip()
            if line and not line.startswith("#"):
                key, value = line.split("=")
                os.environ[key] = value


read_env_file(".env")


def call_ojs_api(method):
    endpoint = os.getenv("BASE_URL")
    url = endpoint + method
    api_token = os.getenv("API_TOKEN")

    headers = {
        "Authorization": f"Bearer {api_token}",
        "Content-Type": "application/json",
    }

    print(f"Calling: {url}")
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        try:
            data = response.json()
            # print(json.dumps(response.json(), indent=4))
            return data
        except requests.exceptions.JSONDecodeError:
            print(f"Content: {response.content}")
    else:
        print(f"Error {response.status_code}: {response.text}")


# Export contents About
def getAbout():
    call_ojs_api("about")


# Export article documentation
def count_articles():
    data = call_ojs_api("submissions")

    publications = []

    status_count = {}

    # Iterate through the data object
    for key, value in data.items():

        for pub_key, pub_value in value["_data"]["publications"].items():
            publication = pub_value["_data"]
            # Create a simplified publication object
            pub_object = {
                "id": publication["id"],
                "title": publication["title"]["en"],
                "abstract": publication["abstract"]["en"],
                "authors": [
                    {
                        "id": author["_data"]["id"],
                        "country": author["_data"].get("country", ""),
                    }
                    for author in publication["authors"].values()
                ],
                "status": publication["status"],
            }
            status = publication["status"]
            status_name = status_names.get(status, f"STATUS_UNKNOWN_{status}")

            if status_name in status_count:
                status_count[status_name] += 1
            else:
                status_count[status_name] = 1

            publications.append(pub_object)
    print(json.dumps(status_count, indent=4))


# Export author data
def data_author():
    data = call_ojs_api("author")

    authors = []

    for key, value in data.items():
        author = value["_data"]

        pub_object = {
            "id": author["id"],
            "affiliation": author.get("affiliation", {}).get("en", ""),
            "familyName": author.get("familyName", {}).get("en", ""),
            "givenName": author.get("givenName", {}).get("en", ""),
        }

        authors.append(pub_object)
    print(json.dumps(authors, indent=4))


# Export reviewer data
def get_reviewers():
    data = call_ojs_api("reviewers")

    reviewers = []

    for key, value in data.items():
        reviewer = value["_data"]

        pub_object = {
            "id": reviewer["id"],
            "affiliation": reviewer.get("affiliation", {}).get("en", ""),
            "familyName": reviewer.get("familyName", {}).get("en", ""),
            "givenName": reviewer.get("givenName", {}).get("en", ""),
        }
        reviewers.append(pub_object)

    print(json.dumps(reviewers, indent=4))


# Export issues documentation
def get_issues():
    data = call_ojs_api("issues")

    items = []

    for value in data:
        item = value["_data"]

        submissions = []
        for submission_id, submission in item.get("submissions", {}).items():
            publications = []
            if "publications" in submission.get("_data", {}):
                for publication_id, publication in submission["_data"][
                    "publications"
                ].items():
                    title = publication["_data"].get("title", {}).get("en", "")
                    authors = []
                    if "authors" in publication["_data"]:
                        for author_id, author in publication["_data"][
                            "authors"
                        ].items():
                            authors.append(
                                {
                                    "id": author["_data"]["id"],
                                    "email": author["_data"]["email"],
                                    "familyName": author["_data"]
                                    .get("familyName", {})
                                    .get("en", ""),
                                    "givenName": author["_data"]
                                    .get("givenName", {})
                                    .get("en", ""),
                                }
                            )

                    publications.append(
                        {
                            "id": publication["_data"]["id"],
                            "title": title,
                            "authors": authors,
                        }
                    )

            submissions.append(
                {
                    "id": submission["_data"]["id"],
                    "publications": publications,
                }
            )

        pub_object = {
            "id": item["id"],
            "volume": item.get("volume", {}),
            "number": item.get("number", {}),
            "year": item.get("year", {}),
            "submissions": submissions,
        }
        items.append(pub_object)

    print(json.dumps(items, indent=4))


# Export journal identification data
def get_journal_identy():
    data = call_ojs_api("journalIdentity")

    items = []

    for value in data:
        item = value["_data"]

        pub_object = {
            "name": item["name"].get("en", ""),
            "printIssn": item.get("printIssn", {}),
            "onlineIssn": item.get("onlineIssn", {}),
            "publisherInstitution": item.get("publisherInstitution", {}),
        }
        items.append(pub_object)


    print(json.dumps(items, indent=4))


# Export information from the article submission page
def get_submission_info():
    data = call_ojs_api("journalIdentity")
    sections = call_ojs_api("section")

    itemsSections = []

    for key, value in sections.items():
        item = value["_data"]

        pub_object = {
            "title": item["title"].get("en", ""),
            "policy": item["policy"].get("en", ""),
        }
        itemsSections.append(pub_object)


    items = []

    for value in data:
        item = value["_data"]

        pub_object = {
            "name": item["name"].get("en", ""),
            "authorGuidelines": item.get("authorGuidelines", {}),
            "submissionChecklist": item["submissionChecklist"].get("en", ""),
            "copyrightNotice": item.get("copyrightNotice", {}).get("en", ""),
            "privacyStatement": item.get("privacyStatement",{}).get("en", ""),
            "sections":itemsSections
        }
        items.append(pub_object)

    print(json.dumps(items, indent=4))


get_submission_info()

# Export a summary of the last year and statistics on submissions and reviewers
# Export URLs

# Export all archives-documentation

# Export editorial
# Export editorial flow of the selected submission
