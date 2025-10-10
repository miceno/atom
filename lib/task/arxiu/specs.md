Implement a match algorithm of an author to a list of authors. match according to the following rules:
    1. Each author is represented as an object with the following fields:
        - actorId
        - name: the source name of the author, e.g. "Last, First".
        - nameId: the id of the original author name for author names with parallel forms.
        - norm_name: normalized name of the author, e.g. "last, first", it is normalized according to the following rules (method normalizeName):
            - normalize accents in UTF-8
            - all letters are lowercase
            - all spaces are replaced with a single space
            - all non-alphanumeric characters are removed, except for comma
            - opening and closing parentheses are removed
    2. For efficient matching, build an index (associative array) of authors by norm_name:
        - normNameIndex[norm_name] = array of authors with that norm_name
        - This allows for O(1) lookup for exact norm_name matches and efficient grouping for comma-based logic.
    3. Match the normalized name of each author. For source authors with a comma (in the format "Last [Second], First"):
        - Attempt to match using the first name and the full last name (including the optional second last name). If they match exactly, this is a match.
        - If no match is found, attempt to match using the first name and only the first last name (ignoring the second last name). If they match, this is a match.
        - Use the normNameIndex to quickly find matches for both cases.
        - Only the first and (optional) second last names and the first name are considered for matching. Any additional names or fields are ignored.
    4. For source authors without a comma (non-standard format):
        - Match the name as a whole using the normNameIndex for efficient lookup.
    5. Skip authors with the same actorId.
- If a source author matches multiple authors in the list, all matches should be returned.
