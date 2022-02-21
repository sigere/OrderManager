class Subject {
    constructor(id, content, type) {
        this.id = id;
        this.content = content;
        this.type = type;
    }
}

class DetailsController {
    constructor(idHolder, contentHolder, subjectType, onUpdate) {
        this.idHolder = idHolder;
        this.contentHolder = contentHolder;
        this.subjectType = subjectType;
        this.current = null;
        this.onUpdate = onUpdate;

        $.query.get(subjectType);
        if (idHolder.innerHTML !== "") {

        }
    }

    executeAfter(executable, stamp) {
        setTimeout(
            executable,
            (stamp - Date.now()) > 0 ? (stamp - Date.now()) : 0
        );
    }

    hide() {
        if (!this.contentHolder.classList.contains("hidden")) {
            this.contentHolder.classList.toggle("hidden")
        }
    }

    expose() {
        if (this.contentHolder.classList.contains("hidden")) {
            this.contentHolder.classList.toggle("hidden")
        }
    }

    reload(id) {
        let storedThis = this;

        let request = new XMLHttpRequest();
        request.open(
            "GET",
            this.subjectType + "/" + id,
            true
        );

        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        request.onload = function (oEvent) {
            storedThis.executeAfter(function () {
                storedThis.contentHolder.innerHTML = request.responseText;
                storedThis.idHolder.innerHTML = id;
                storedThis.current = new Subject(
                    Number(id),
                    request.responseText,
                    storedThis.subjectType
                );
                storedThis.pushState({
                    "content": request.responseText,
                    "id": id,
                    "type": storedThis.subjectType
                });
                storedThis.expose();
                storedThis.onUpdate();
            }, Date.now() + 400);
        };

        this.hide();
        request.send();
    }

    pushState(subject) {
        window.history.pushState(
            {
                "content": subject.content,
                "id": subject.id
            },
            "",
            $.query.set(this.subjectType, subject.id)
        );
    }

    popState(event) {
        let subject = event.state;
        let storedThis = this;
        this.hide();
        this.executeAfter(function () {
            storedThis.contentHolder.innerHTML = subject.content;
            storedThis.idHolder.innerHTML = subject.id;
            storedThis.currentId = Number(subject.id);
            storedThis.expose();
        }, Date.now() + 400);
    }
}