from Engine import document
from Engine import family
from xml.dom import minidom
import os

def parse(evaluation,repositoryroot="../.."):
    """Parses an evaluation
    
    Parameter : 
        - evaluation    -   string of evaluation XML flow
        
    Returns document"""
    #Transform XML flow into document object
    document = createDocument(evaluation)
    
    #Create tree folder in filesystem
    path = os.path.join(repositoryroot,"sheets","evaluations",document["properties"][1],document["properties"][2])
    try :
        os.makedirs(path)
    except OSError :
        pass
    f = open(os.path.join(path,"foo"),"w")
    f.close()
    

def createDocument(evaluation,familypath="../../sheets/families"):
    rawDocument = minidom.parseString(evaluation)
    #Define the ID attribute of each element tag of the raw document
    for element in rawDocument.getElementsByTagName("element"):
        element.setIdAttribute("name")
        
    #Create the property list from the content of header.
    #the first, second and last tag of header are ignored
    #as they are not document properties but part of families contents
    header = rawDocument.firstChild.firstChild.childNodes
    properties = [str(node.firstChild.data) for node in header[2:-2]]
    
    #Instantiate a QSOS-Document object initiated with the properties extracted
    #from XML evaluation and empty family dictionnary
    qsos = document.Document(properties,{})
    
    #Extract  relevant information from the raw evaluation:
    #    - authors
    #    - dates
    #    - families (generic section is manually as it appears in each evaluation
    
    # TODO : Each information should be probed in case no values are
    #         provided in the XML document
    
    authors = [(item.firstChild.firstChild.data, item.lastChild.firstChild.data) for item in header[0].childNodes]
    dates = (header[1].firstChild.firstChild.data, header[1].lastChild.firstChild.data)
    families = [node.firstChild.data for node in header[-1].childNodes]
    families.insert(0,"generic")

    #Build the Family object for each family component of the evaluation :
    #    - Extract from repository the family sheet (.qin files)
    #    - Read the scores and comments from evaluation
    #    - Update entry in family object
    for include in families :
        template = minidom.parse("/".join([familypath,".".join([families[0],"qin"])]))
        #Initiate the family object : 
        #    -same authors and dates for all families of the same evaluation
        #    -empty score and comments dictionnary
        f = family.family(authors, dates)
        for element in template.getElementsByTagName("desc0"):
            name = element.parentNode.getAttribute("name")
            
            # TODO : use a logger for AttributeError exception^^
        
            try :
                f.scores[name] = rawDocument.getElementById(name).getElementsByTagName("score").item(0).firstChild.data
            except AttributeError :
                print "No score found for element %s" % (name,)
            try :
                f.comments[name] = rawDocument.getElementById(name).getElementsByTagName("comment").item(0).firstChild.data
            except AttributeError :
                print "No comment found for element %s" % (name,)
        #End of iteration, just add the family in document object
        qsos.families[include] = f
        print createScore(qsos.families["generic"])
    return qsos

def createScore(family):
    """Creates the XML document to be stored on the 
    filesystem of the evaluation from a family object
    
    Returns : string    String flow of the family object"""
    
    #Create the return DOM and root element <qsosscore>
    document = minidom.Document()
    root = document.createElement("qsosscore")
    
    #Build header which contains only author and dates
    header = document.createElement("header")
    for author in family["authors"] :
        tag = document.createElement("author")
        leaf = document.createElement("name")
        leaf.appendChild(document.createTextNode(author[0]))
        tag.appendChild(leaf)
        leaf = document.createElement("email")
        leaf.appendChild(document.createTextNode(author[1]))
        tag.appendChild(leaf)                           
        header.appendChild(tag)
    
    tag = document.createElement("dates")
    leaf = document.createElement("creation")
    leaf.appendChild(document.createTextNode(family["date.creation"]))
    tag.appendChild(leaf)
    leaf = document.createElement("validation")
    leaf.appendChild(document.createTextNode(family["date.validation"]))
    tag.appendChild(leaf)
    header.appendChild(tag)
    
    #Build score section
    #Local copies of family's attribute are made as destructive
    #iterator are used.
    section = document.createElement("scores")
    scores = family.scores.copy()
    comments = family.comments.copy()
    
    #Elements with score tags are generated first. Corresponding
    #comments are also added to elements tag (and removed from
    #comments dictionnary)
    while scores :
        (name,value) = scores.popitem()
        tag = document.createElement("element")
        tag.setAttribute("name", name)
        leaf = document.createElement("score")
        leaf.appendChild(document.createTextNode(value))
        tag.appendChild(leaf)
        if name in comments :
            leaf = document.createElement("comment")
            leaf.appendChild(document.createTextNode(comments.pop(name)))
            tag.appendChild(leaf)
        section.appendChild(tag)
    
    #Remaining items of comments dictionnary are added to output XML
    #No score tags for these elements as there must be no item left in scores 
    while comments :
        (name, value) = comments.popitem()
        tag = document.createElement("element")
        tag.setAttribute("name", name)
        leaf = document.createElement("comment")
        leaf.appendChild(document.createTextNode(value))
        tag.appendChild(leaf)
        section.appendChild(tag)
    
    #Build the final document
    root.appendChild(header)
    root.appendChild(section)
    document.appendChild(root)    
    
    #Pretty format ant return the result qsos score sheet
    return document.toprettyxml("\t", "\n", "utf-8")